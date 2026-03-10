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

class PickupTrackingController extends Controller
{
    /**
     * Start tracking for customer picking up from shop
     */
    public function startTracking(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->delivery_method !== 'pickup') {
            return response()->json(['success' => false, 'message' => 'Hanya untuk mode pickup'], 400);
        }

        // Find or create shipment
        // Menggunakan Shipment::firstOrCreate langsung untuk menghindari error ambiguous column
        // yang disebabkan oleh relationship deliveryShipment() yang menggunakan latestOfMany()
        $shipment = Shipment::firstOrCreate([
            'order_id' => $order->id,
            'type' => Shipment::TYPE_DELIVERY
        ], [
            'status' => Shipment::STATUS_PENDING
        ]);

        // Transition states if needed
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
     * Update customer location and calculate distance to shop
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

        // Calculate distance to shop
        $responseData = ['success' => true];
        $shop = $order->productRental->product->shop;

        if ($shop && $shop->latitude && $shop->longitude) {
            $distance = $shipment->calculateDistanceTo($shop->latitude, $shop->longitude);
            $canArrive = $distance !== null && $distance <= Shipment::ARRIVAL_THRESHOLD;

            $responseData['distance'] = $distance;
            $responseData['can_arrive'] = $canArrive;
            $responseData['threshold'] = Shipment::ARRIVAL_THRESHOLD;

            // 🔥 Proximity Check: If closer than 200 meters, notify both parties
            // Use Cache lock to prevent spam (expires in 1 hour)
            if ($distance !== null && $distance <= 200) {
                $proximityLockKey = "pickup_proximity_alert_{$order->id}";

                if (!\Illuminate\Support\Facades\Cache::has($proximityLockKey)) {
                    \Illuminate\Support\Facades\Cache::put($proximityLockKey, true, now()->addHour());

                    // Notify Customer "Anda sudah dekat"
                    \App\Helpers\CustomerNotificationHelper::notifyNearShop($order);

                    // Notify Seller "Customer sudah dekat"
                    \App\Helpers\CourierNotificationHelper::notifyCustomerNear($order);
                }
            }
        }

        return response()->json($responseData);
    }

    /**
     * Stop tracking (Arrival confirmation)
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

        // Validate current GPS position (optional but recommended)
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
