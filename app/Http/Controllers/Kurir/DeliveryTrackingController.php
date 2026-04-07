<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\TrackingStarted;
use App\Events\LocationUpdated;
use App\Events\TrackingStopped;

class DeliveryTrackingController extends Controller
{
/**
 * Start delivery trip for courier
 */
public function startTracking(Request $request, $id)
{
    // Ambil data kurir berdasarkan user login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // Ambil order berdasarkan ID dari route
    $order = Order::findOrFail($id);

    // ===============================
    // 🔍 CARI SHIPMENT YANG VALID
    // ===============================
    // Cari shipment berdasarkan:
    // - order_id
    // - courier_id (harus milik kurir ini)
    // - status tertentu (assigned / picked_up / on_the_way)
    // - type delivery
    $shipment = Shipment::where('order_id', $id)
        ->where('courier_id', $courier->id)
        ->whereIn('status', [
            Shipment::STATUS_ASSIGNED,
            Shipment::STATUS_PICKED_UP,
            Shipment::STATUS_ON_THE_WAY
        ])
        ->where('type', Shipment::TYPE_DELIVERY)
        ->latest('updated_at') // ambil yang paling baru
        ->first();

    // Kalau tidak ada shipment → error (bukan milik kurir / tidak valid)
    if (!$shipment) {
        \Illuminate\Support\Facades\Log::warning('StartTracking failed', [
            'order_id' => $id,
            'courier_id' => $courier->id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Shipment not found or unauthorized'
        ], 403);
    }

    // ===============================
    // 🔄 HANDLE DUPLIKASI SHIPMENT
    // ===============================
    // Kalau status masih ASSIGNED, cek apakah ada yang lebih "maju"
    if ($shipment->status === Shipment::STATUS_ASSIGNED) {

        $betterShipment = Shipment::where('order_id', $id)
            ->where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->whereIn('status', [
                Shipment::STATUS_PICKED_UP,
                Shipment::STATUS_ON_THE_WAY
            ])
            ->latest('updated_at')
            ->first();

        // Kalau ada shipment yang lebih lanjut → pakai itu
        if ($betterShipment) {
            $shipment = $betterShipment;
        }
    }

    // ===============================
    // ✅ VALIDASI BOLEH START ATAU TIDAK
    // ===============================
    $canStart = false;

    // Untuk DELIVERY:
    // hanya boleh start jika status:
    // - sudah PICKED_UP (barang sudah diambil)
    // - atau sudah ON_THE_WAY (lanjutan)
    if ($shipment->type === Shipment::TYPE_DELIVERY) {
        if (
            $shipment->status === Shipment::STATUS_PICKED_UP ||
            $shipment->status === Shipment::STATUS_ON_THE_WAY
        ) {
            $canStart = true;
        }
    }

    // Kalau tidak boleh start → kirim error
    if (!$canStart) {
        return response()->json([
            'success' => false,
            'message' => 'Harus scan QR dulu sebelum jalan'
        ], 403);
    }

    // ===============================
    // 🚀 UBAH STATUS → ON THE WAY
    // ===============================
    $shipment->markAsOnTheWay();

    // ===============================
    // 🔔 NOTIFIKASI
    // ===============================

    // Ke customer: pesanan sedang dikirim
    \App\Helpers\CourierNotificationHelper::notifyAcceptance($order, $courier);

    // Ke seller: kurir sedang jalan
    \App\Helpers\CourierNotificationHelper::notifySellerInTransit($order, $courier);

    // ===============================
    // 📡 REALTIME BROADCAST
    // ===============================
    broadcast(new TrackingStarted($order))->toOthers();

    return response()->json(['status' => 'success']);
}
/**
 * Update courier location during delivery
 */
public function updateLocation(Request $request)
{
    // ===============================
    // ✅ VALIDASI INPUT
    // ===============================
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
    ]);

    // Ambil kurir login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // ===============================
    // 🔍 AMBIL SHIPMENT AKTIF
    // ===============================
    $shipment = Shipment::where('order_id', $request->order_id)
        ->where('courier_id', $courier->id)
        ->where('type', Shipment::TYPE_DELIVERY)
        ->where('is_tracking_active', true)
        ->first();

    // Kalau tidak ada → tracking belum aktif
    if (!$shipment) {
        return response()->json([
            'success' => false,
            'message' => 'Tracking tidak aktif'
        ], 400);
    }

    // ===============================
    // 📍 UPDATE LOKASI
    // ===============================
    $shipment->updateLocation($request->lat, $request->lng);

    // Broadcast ke frontend (realtime map)
    broadcast(new LocationUpdated(
        $request->order_id,
        $request->lat,
        $request->lng
    ))->toOthers();

    // ===============================
    // 📏 DETEKSI SAMPAI (AUTO)
    // ===============================
    $arrived = false;

    // Ambil koordinat tujuan (customer)
    $destLat = $shipment->order->address?->latitude;
    $destLng = $shipment->order->address?->longitude;

    if ($destLat && $destLng) {

        // Hitung jarak kurir ke tujuan
        $distance = Shipment::calculateDistance(
            $request->lat,
            $request->lng,
            $destLat,
            $destLng
        );

        // Kalau jarak <= threshold → dianggap sampai
        if ($distance <= Shipment::ARRIVAL_THRESHOLD) {

            // Update status hanya jika masih OTW
            if ($shipment->status === Shipment::STATUS_ON_THE_WAY) {
                $shipment->update([
                    'status' => Shipment::STATUS_ARRIVED,
                    'is_tracking_active' => true,
                ]);

                $arrived = true;

                // 🔔 Notifikasi ke customer
                \App\Helpers\CustomerNotificationHelper::notifyOrderArrived($shipment->order);

                // Stop tracking di frontend
                broadcast(new TrackingStopped($shipment->order))->toOthers();
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'distance' => $distance ?? null,
        'arrived' => $arrived
    ]);
}
/**
 * Mark as arrived at customer location
 */
public function stopTracking(Request $request, $id)
{
    // Ambil kurir login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // ===============================
    // 🔍 AMBIL SHIPMENT TERKAIT
    // ===============================
    $shipment = Shipment::where('order_id', $id)
        ->where('courier_id', $courier->id)
        ->where('type', Shipment::TYPE_DELIVERY)
        ->latest('updated_at')
        ->first();

    // Kalau tidak ada → unauthorized
    if (!$shipment) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    // Kalau tracking sudah berhenti → skip
    if (!$shipment->is_tracking_active) {
        return response()->json(['success' => true]);
    }

    // ===============================
    // 🛑 STOP TRACKING
    // ===============================
    $shipment->update([
        'is_tracking_active' => false,
        'status' => Shipment::STATUS_ARRIVED,
    ]);

    // ===============================
    // 🔔 NOTIFIKASI
    // ===============================
    \App\Helpers\CustomerNotificationHelper::notifyOrderArrived(
        $shipment->order ?? Order::find($id)
    );

    // Broadcast realtime
    broadcast(new TrackingStopped(
        $shipment->order ?? Order::find($id)
    ))->toOthers();

    return response()->json(['success' => true]);
}

}
