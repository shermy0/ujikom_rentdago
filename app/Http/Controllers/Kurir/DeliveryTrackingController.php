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
        $courier = Courier::where('user_id', Auth::id())->firstOrFail();

        $order = Order::findOrFail($id);
        $shipment = $order->deliveryShipment; // Assuming this works for return too? Check model.
        // Wait, Order model has deliveryShipment and returnShipment.
        // We need to fetch the CORRECT shipment based on the request or check both?
        // Let's assume the ID passed is Order ID as per route.
        // We should check if we are in Return mode.

        // Better: Find shipment by order_id that belongs to this courier
        // This handles both Delivery and Return automatically
        $shipment = Shipment::where('order_id', $id)
            ->where('courier_id', $courier->id)
            ->whereIn('status', [
                Shipment::STATUS_ASSIGNED,
                Shipment::STATUS_PICKED_UP,
                Shipment::STATUS_ON_THE_WAY
            ])
            ->where('type', Shipment::TYPE_DELIVERY)
            ->latest('updated_at') // Fix: Use updated_at to get the most recently active shipment (matching View logic)
            ->first();

        if (!$shipment) {
            \Illuminate\Support\Facades\Log::warning('StartTracking failed: No active shipment found', [
                'order_id' => $id,
                'courier_id' => $courier->id
            ]);
            return response()->json(['success' => false, 'message' => 'Shipment not found or unauthorized'], 403);
        }

        // Additional safeguard: If multiple active shipments exist, prefer PICKED_UP/OTW over ASSIGNED
        // (This handles rare cases of duplicate active shipments)
        if ($shipment->status === Shipment::STATUS_ASSIGNED) {
            $betterShipment = Shipment::where('order_id', $id)
                ->where('courier_id', $courier->id)
                ->where('type', Shipment::TYPE_DELIVERY)
                ->whereIn('status', [Shipment::STATUS_PICKED_UP, Shipment::STATUS_ON_THE_WAY])
                ->latest('updated_at')
                ->first();

            if ($betterShipment) {
                $shipment = $betterShipment;
            }
        }

        // VALIDATION:
        // 1. Delivery: Must be PICKED_UP to start OTW. (Start from Shop)
        // 2. Return (Leg 1): Must be ASSIGNED to start OTW. (Start from Shop -> Customer)
        // 3. Return (Leg 2): Must be PICKED_UP to start OTW. (Start from Customer -> Shop)

        $canStart = false;

        if ($shipment->type === Shipment::TYPE_DELIVERY) {
            if ($shipment->status === Shipment::STATUS_PICKED_UP || $shipment->status === Shipment::STATUS_ON_THE_WAY) {
                $canStart = true;
            }
        }

        if (!$canStart) {
            $msg = ($shipment->type === Shipment::TYPE_DELIVERY)
                ? 'Anda harus melakukan scan QR pengambilan terlebih dahulu.'
                : 'Status pengiriman tidak valid untuk memulai perjalanan.';

            // DEBUG INFO appended to message
            $debugInfo = " (Status: " . $shipment->status . ", Type: " . $shipment->type . ", ID: " . $shipment->id . ")";

            return response()->json([
                'success' => false,
                'message' => $msg . $debugInfo
            ], 403);
        }

        $shipment->markAsOnTheWay();

        // 🔔 Notify Customer: Pesanan Dalam Perjalanan (ONLY when status becomes on_the_way)
        if ($shipment->type === Shipment::TYPE_DELIVERY) {
            \App\Helpers\CourierNotificationHelper::notifyAcceptance($order, $courier);
        }

        // 🔔 Notify Seller
        // Only notify "In Transit" if it's delivery
        if ($shipment->type === Shipment::TYPE_DELIVERY) {
            \App\Helpers\CourierNotificationHelper::notifySellerInTransit($order, $courier);
        }

        broadcast(new TrackingStarted($order))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * Update courier location during delivery
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $courier = Courier::where('user_id', Auth::id())->firstOrFail();

        $shipment = Shipment::where('order_id', $request->order_id)
            ->where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->where('is_tracking_active', true)
            ->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Tracking tidak aktif'], 400);
        }

        // 1. Update Current Location
        $shipment->updateLocation($request->lat, $request->lng);

        broadcast(new LocationUpdated($request->order_id, $request->lat, $request->lng))->toOthers();

        // 2. Auto-Arrival Detection
        $arrived = false;
        $distance = null;

        // Delivery: Always to Customer
        $destLat = $shipment->order->address?->latitude;
        $destLng = $shipment->order->address?->longitude;

        // Calculate and Process Arrival
        if ($destLat && $destLng) {
            $distance = Shipment::calculateDistance($request->lat, $request->lng, $destLat, $destLng);

            // If within threshold, mark as arrived
            if ($distance <= Shipment::ARRIVAL_THRESHOLD) {
                // Only update status if currently ON_THE_WAY
                if ($shipment->status === Shipment::STATUS_ON_THE_WAY) {
                    $shipment->update([
                        'status' => Shipment::STATUS_ARRIVED,
                        'is_tracking_active' => true,
                    ]);
                    $arrived = true;
                    
                    // 🔔 Notify Customer: Kurir Sudah Sampai
                    \App\Helpers\CustomerNotificationHelper::notifyOrderArrived($shipment->order);
                    
                    broadcast(new TrackingStopped($shipment->order))->toOthers();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'distance' => $distance,
            'arrived' => $arrived || ($shipment->status === Shipment::STATUS_ARRIVED)
        ]);
    }

    /**
     * Mark as arrived at customer location
     */
    public function stopTracking(Request $request, $id)
    {
        $courier = Courier::where('user_id', Auth::id())->firstOrFail();

        // Use precise query similar to startTracking
        $shipment = Shipment::where('order_id', $id)
            ->where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY) // Ensure type is DELIVERY
            ->latest('updated_at')
            ->first();

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$shipment->is_tracking_active) {
            return response()->json(['success' => true]); // Already stopped
        }

        $shipment->update([
            'is_tracking_active' => false,
            'status' => Shipment::STATUS_ARRIVED,
        ]);

        // 🔔 Notify Customer: Kurir Sudah Sampai
        \App\Helpers\CustomerNotificationHelper::notifyOrderArrived($shipment->order ?? Order::find($id));

        broadcast(new TrackingStopped($shipment->order ?? Order::find($id)))->toOthers();

        return response()->json(['success' => true]);
    }
}
