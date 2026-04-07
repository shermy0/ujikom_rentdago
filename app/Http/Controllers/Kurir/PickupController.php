<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PickupController extends Controller
{
/**
 * Show QR Scanner for pickup
 */
public function showScan($orderId)
{
    // 🔍 Ambil data kurir berdasarkan user login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // 🔍 Ambil data order berdasarkan ID
    $order = Order::findOrFail($orderId);

    // 🔍 Ambil shipment delivery dari order tersebut
    $shipment = $order->deliveryShipment;

    // ❌ Validasi:
    // - shipment harus ada
    // - shipment harus milik kurir ini
    if (!$shipment || $shipment->courier_id !== $courier->id) {
        return redirect()->route('kurir.orders')->with('error', 'Unauthorized access.');
    }

    // ❌ Validasi status:
    // hanya boleh scan jika status masih ASSIGNED (siap diambil)
    if ($shipment->status !== Shipment::STATUS_ASSIGNED) {
        return redirect()->route('kurir.orders')->with('error', 'Pesanan tidak dalam status siap ambil.');
    }

    // 🎯 Tampilkan halaman scan QR
    return view('kurir.pickup.scan', compact('order', 'shipment'));
}

/**
 * Verify QR for pickup
 */
public function verifyPickup(Request $request)
{
    // ✅ Validasi input dari request
    $request->validate([
        'order_code' => 'required|string',           // kode order dari QR
        'order_id' => 'required|exists:orders,id'    // ID order harus valid
    ]);

    // 🔍 Ambil data kurir login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // 🔍 Cari order berdasarkan:
    // - ID
    // - order_code (biar QR tidak bisa dipalsukan)
    $order = Order::where('id', $request->order_id)
        ->where('order_code', $request->order_code)
        ->first();

    // ❌ Jika order tidak cocok → QR invalid
    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'QR Code tidak valid atau tidak cocok dengan pesanan.'
        ], 422);
    }

    // 🔍 Ambil shipment delivery dari order
    $shipment = $order->deliveryShipment;

    // ❌ Validasi:
    // shipment harus ada & harus milik kurir ini
    if (!$shipment || $shipment->courier_id !== $courier->id) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak ditugaskan untuk pesanan ini.'
        ], 403);
    }

    // ❌ Validasi status:
    // hanya boleh pickup jika status masih ASSIGNED
    if ($shipment->status !== Shipment::STATUS_ASSIGNED) {
        return response()->json([
            'success' => false,
            'message' => 'Barang sudah pernah diambil atau status tidak valid.'
        ], 422);
    }

    try {
        // 📦 Update shipment:
        // - status jadi PICKED_UP (barang sudah diambil)
        // - simpan waktu pengambilan
        // - simpan lokasi terakhir kurir
        $shipment->update([
            'status' => Shipment::STATUS_PICKED_UP,
            'picked_up_at' => now(),
            'last_lat' => $request->lat,
            'last_lng' => $request->lng,
        ]);

        // 📝 Log aktivitas pickup (untuk histori/debug)
        Log::info('Courier picked up item via QR', [
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'location' => [
                'lat' => $request->lat,
                'lng' => $request->lng
            ]
        ]);

        // ✅ Response sukses
        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diambil! Silakan mulai pengantaran.',
            'redirect' => route('kurir.orders')
        ]);

    } catch (\Exception $e) {

        // ❌ Jika error → log error
        Log::error('Pickup verification error: ' . $e->getMessage());

        // ❌ Response gagal
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memproses data.'
        ], 500);
    }
}}
