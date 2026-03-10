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
        $courier = Courier::where('user_id', Auth::id())->firstOrFail();
        $order = Order::findOrFail($orderId);
        $shipment = $order->deliveryShipment;

        if (!$shipment || $shipment->courier_id !== $courier->id) {
            return redirect()->route('kurir.orders')->with('error', 'Unauthorized access.');
        }

        if ($shipment->status !== Shipment::STATUS_ASSIGNED) {
            return redirect()->route('kurir.orders')->with('error', 'Pesanan tidak dalam status siap ambil.');
        }

        return view('kurir.pickup.scan', compact('order', 'shipment'));
    }

    /**
     * Verify QR for pickup
     */
    public function verifyPickup(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        $courier = Courier::where('user_id', Auth::id())->firstOrFail();
        $order = Order::where('id', $request->order_id)
            ->where('order_code', $request->order_code)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau tidak cocok dengan pesanan.'
            ], 422);
        }

        $shipment = $order->deliveryShipment;

        if (!$shipment || $shipment->courier_id !== $courier->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan untuk pesanan ini.'
            ], 403);
        }

        if ($shipment->status !== Shipment::STATUS_ASSIGNED) {
            return response()->json([
                'success' => false,
                'message' => 'Barang sudah pernah diambil atau status tidak valid.'
            ], 422);
        }

        try {
            // Update shipment status
            $shipment->update([
                'status' => Shipment::STATUS_PICKED_UP,
                'picked_up_at' => now(),
                'last_lat' => $request->lat,
                'last_lng' => $request->lng,
            ]);

            Log::info('Courier picked up item via QR', [
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'location' => ['lat' => $request->lat, 'lng' => $request->lng]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diambil! Silakan mulai pengantaran.',
                'redirect' => route('kurir.orders')
            ]);
        } catch (\Exception $e) {
            Log::error('Pickup verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data.'
            ], 500);
        }
    }
}
