<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\TrackingStarted;
use App\Events\LocationUpdated;
use App\Events\TrackingStopped;

/**
 * PickupTrackingController — Mengelola pelacakan lokasi Customer saat pengambilan (pickup).
 *
 * Menyediakan fitur mulai tracking, update lokasi secara berkala,
 * dan konfirmasi sampai di toko. Menggunakan Pusher/WebSocket
 * melalui Laravel Events untuk menyiarkan perubahan posisi secara real-time.
 */
class PickupTrackingController extends Controller
{
    /**
     * Memulai sesi tracking untuk customer yang akan mengambil barang ke toko.
     *
     * Membuat atau menemukan record Shipment, lalu mengubah statusnya
     * menjadi on_the_way dan mengaktifkan tracking.
     * Mengirimkan event TrackingStarted ke semua client yang terhubung.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id ID pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function startTracking(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->delivery_method !== 'pickup') {
            return response()->json(['success' => false, 'message' => 'Hanya untuk mode pickup'], 400);
        }

        // Cari atau buat record Shipment untuk pesanan ini
        // Menggunakan Shipment::firstOrCreate langsung untuk menghindari error ambiguous column
        // yang disebabkan oleh relationship deliveryShipment() yang menggunakan latestOfMany()
        $shipment = Shipment::firstOrCreate([
            'order_id' => $order->id,
            'type' => Shipment::TYPE_DELIVERY
        ], [
            'status' => Shipment::STATUS_PENDING
        ]);

        // Ubah status menjadi on_the_way jika statusnya masih pending atau assigned
        if ($shipment->status === Shipment::STATUS_ASSIGNED || $shipment->status === Shipment::STATUS_PENDING) {
            $shipment->update([
                'status' => Shipment::STATUS_ON_THE_WAY,
                'is_tracking_active' => true,
                'picked_up_at' => now(),
            ]);
        }

        broadcast(new TrackingStarted($order))->toOthers();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('info', 'Tracking pengambilan dimulai. Pastikan izin lokasi aktif.');
    }

    /**
     * Memperbarui posisi lokasi customer dan menghitung jarak ke toko.
     *
     * Menerima koordinat GPS dari frontend, menyimpan posisi terakhir ke Shipment,
     * lalu menyiarkan pembaruan lokasi via WebSocket.
     * Jika jarak kurang dari 200 meter, notifikasi "sudah dekat" dikirim ke
     * customer dan seller (menggunakan Cache lock untuk mencegah spam notifikasi).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $order = Order::where('user_id', Auth::id())->find($request->order_id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan atau akses ditolak'], 403);
        }

        $shipment = $order->deliveryShipment;

        if (!$shipment || !$shipment->is_tracking_active) {
            return response()->json(['success' => false, 'message' => 'Tracking tidak aktif'], 400);
        }

        $shipment->updateLocation($request->latitude, $request->longitude);

        broadcast(new LocationUpdated($order->id, $request->latitude, $request->longitude))->toOthers();

        // Hitung jarak customer ke toko untuk ditampilkan di peta
        $responseData = ['success' => true];
        $shop = $order->productRental->product->shop;

        if ($shop && $shop->latitude && $shop->longitude) {
            $distance = $shipment->calculateDistanceTo($shop->latitude, $shop->longitude);
            $canArrive = $distance !== null && $distance <= Shipment::ARRIVAL_THRESHOLD;

            $responseData['distance'] = $distance;
            $responseData['can_arrive'] = $canArrive;
            $responseData['threshold'] = Shipment::ARRIVAL_THRESHOLD;

            // 🔥 Cek Kedekatan: Jika lebih dekat dari 200 meter, kirim notifikasi ke kedua pihak
            // Gunakan Cache lock untuk mencegah notifikasi spam (kunci kadaluarsa dalam 1 jam)
            if ($distance !== null && $distance <= 200) {
                $proximityLockKey = "pickup_proximity_alert_{$order->id}";

                if (!\Illuminate\Support\Facades\Cache::has($proximityLockKey)) {
                    \Illuminate\Support\Facades\Cache::put($proximityLockKey, true, now()->addHour());

                    // Kirim notifikasi ke Customer "Anda sudah dekat"
                    \App\Helpers\CustomerNotificationHelper::notifyNearShop($order);

                    // Kirim notifikasi ke Seller bahwa "Customer sudah dekat"
                    \App\Helpers\CourierNotificationHelper::notifyCustomerNear($order);
                }
            }
        }

        return response()->json($responseData);
    }

    /**
     * Menghentikan tracking dan mengkonfirmasi customer telah sampai di toko.
     *
     * Memvalidasi posisi GPS saat ini (opsional): jika customer masih terlalu jauh
     * dari toko (melebihi MAX_VALIDATION_THRESHOLD), konfirmasi ditolak.
     * Jika valid, status Shipment diubah menjadi arrived dan event TrackingStopped disiarkan.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id ID pesanan
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function stopTracking(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $shipment = $order->deliveryShipment;

        if (!$shipment || $shipment->status !== Shipment::STATUS_ON_THE_WAY) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Status tidak valid untuk konfirmasi sampai.']);
            }
            return back()->with('error', 'Status tidak valid untuk konfirmasi sampai.');
        }

        // Validasi posisi GPS saat ini (opsional namun disarankan)
        $lat = $request->current_lat ?? $request->latitude;
        $lng = $request->current_lng ?? $request->longitude;

        if ($lat && $lng) {
            $shop = $order->productRental->product->shop;
            if ($shop && $shop->latitude && $shop->longitude) {
                $distance = Shipment::calculateDistance($lat, $lng, $shop->latitude, $shop->longitude);

                if ($distance > Shipment::MAX_VALIDATION_THRESHOLD) {
                    $msg = 'Anda masih terlalu jauh dari toko (' . round($distance) . 'm). Silakan mendekati toko terlebih dahulu.';
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $msg]);
                    }
                    return back()->with('error', $msg);
                }
            }
        }

        $shipment->update([
            'is_tracking_active' => false,
            'status' => Shipment::STATUS_ARRIVED,
        ]);

        broadcast(new TrackingStopped($order))->toOthers();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Anda sudah sampai!']);
        }

        return back()->with('success', 'Anda sudah sampai! Silakan tunjukkan QR Code ke penjual.');
    }
}
