<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Courier;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display list of orders for seller's shop
     */
    public function index()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'You need to create a shop first');
        }

        $orders = Order::with([
            'user',
            'productRental.product',
            'deliveryShipment.courier.user'
        ])
            ->whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('seller.orders.index', compact('orders'));
    }

    /**
     * Show order detail
     */
    public function show($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'You need to create a shop first');
        }

        $order = Order::with([
            'user',
            'productRental.product.images',
            'productRental.product.category',
            'deliveryShipment.courier.user',
            'returnShipment.courier.user',
            'address',
            'orderReturn'
        ])
            ->whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        // Get available couriers for this shop (only active couriers)
        $availableCouriers = collect();

        // Only fetch couriers if the order is a delivery order
        if ($order->delivery_method === 'delivery') {
            $availableCouriers = Courier::where('shop_id', $shop->id)
                ->where('status', 'active')
                ->whereHas('user')
                ->with('user')
                ->get();
        }

        return view('seller.orders.show', compact('order', 'availableCouriers'));
    }
    /**
     * Assign courier to delivery order
     */
    public function assignCourier(Request $request, $id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return back()->with('error', 'You need to create a shop first');
        }

        $order = Order::with(['productRental.product', 'user', 'address'])
            ->whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        // Validate delivery method
        if ($order->delivery_method !== 'delivery') {
            return back()->with('error', 'Courier can only be assigned to delivery orders');
        }

        // Validate payment status (this is the critical check)
        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Order must be paid before assigning courier');
        }

        // Validate order status - allow pending, paid, or confirmed
        // (pending is OK if payment_status is paid)
        if (!in_array($order->status, ['pending', 'paid', 'confirmed'])) {
            return back()->with('error', 'Courier can only be assigned to pending/paid/confirmed orders. Current status: ' . $order->status);
        }

        // Validate customer has at least one address for delivery
        if (!$order->user->addresses()->exists()) {
            return back()->with('error', 'Customer belum memiliki alamat pengiriman. Hubungi customer untuk melengkapi alamat terlebih dahulu.');
        }

        // Check if courier already assigned
        $existingShipment = Shipment::where('order_id', $order->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->first();

        if ($existingShipment) {
            return back()->with('error', 'This order already has an assigned courier');
        }

        // Validate courier belongs to this shop
        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'courier_notes' => 'nullable|string|max:1000',
        ]);

        $courier = Courier::where('id', $request->courier_id)
            ->where('shop_id', $shop->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Prepare address snapshot
        $deliveryAddressSnapshot = null;

        // Try to get address from order first
        $addressToUse = $order->address;

        // Log for debugging
        Log::info('Preparing delivery address snapshot', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'user_name' => $order->user->name,
            'has_order_address' => !is_null($order->address),
            'user_addresses_count' => $order->user->addresses()->count(),
        ]);

        // Fallback: If no address in order, get customer's default or first address
        if (!$addressToUse) {
            $addressToUse = $order->user->addresses()
                ->where('is_default', true)
                ->first();

            Log::info('Trying default address', [
                'found' => !is_null($addressToUse)
            ]);

            // If still no default, get first address
            if (!$addressToUse) {
                $addressToUse = $order->user->addresses()->first();

                Log::info('Trying first address', [
                    'found' => !is_null($addressToUse)
                ]);
            }
        }

        // Build address snapshot if we have an address
        if ($addressToUse) {
            // Build the address parts
            $addressParts = [];

            // Receiver name (fallback to user name)
            if (!empty($addressToUse->receiver_name)) {
                $addressParts[] = $addressToUse->receiver_name;
            } else if (!empty($order->user->name)) {
                $addressParts[] = $order->user->name;
            }

            // Phone (fallback to user phone)
            if (!empty($addressToUse->receiver_phone)) {
                $addressParts[] = $addressToUse->receiver_phone;
            } else if (!empty($order->user->phone)) {
                $addressParts[] = $order->user->phone;
            }

            // Full address
            if (!empty($addressToUse->address)) {
                $addressParts[] = $addressToUse->address;
            }

            $deliveryAddressSnapshot = implode("\n", $addressParts);

            // Add notes if available
            if (!empty($addressToUse->notes)) {
                $deliveryAddressSnapshot .= "\nCatatan: " . $addressToUse->notes;
            }

            Log::info('Address snapshot created', [
                'snapshot' => $deliveryAddressSnapshot
            ]);
        } else {
            // Last fallback: Use user's basic info with warning
            $deliveryAddressSnapshot = $order->user->name . "\n" . $order->user->phone . "\n(Alamat belum dilengkapi)";

            Log::warning('No address found for user, using fallback', [
                'user_id' => $order->user_id,
                'snapshot' => $deliveryAddressSnapshot
            ]);
        }

        // Prepare pickup address (shop address)
        $pickupAddressSnapshot = $this->getShopAddressSnapshot($shop);

        // Create delivery shipment - status pending until courier accepts
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'type' => Shipment::TYPE_DELIVERY,
            'status' => Shipment::STATUS_PENDING,
            'pickup_address_snapshot' => $pickupAddressSnapshot,
            'delivery_address_snapshot' => $deliveryAddressSnapshot,
            'courier_notes' => $request->courier_notes,
            'assigned_at' => now(),
        ]);

        // Update order status to confirmed if it's still paid
        if ($order->status === 'paid') {
            $order->update(['status' => Order::STATUS_CONFIRMED]);
        }

        // Send WhatsApp notification to courier
        \App\Helpers\CourierNotificationHelper::notifyCourierAssignment($order, $courier, $shipment);

        Log::info('Courier assigned to order', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'courier_id' => $courier->id,
                'shipment_id' => $shipment->id,
                'assigned_by' => Auth::id()
            ]);

            return back()->with('success', 'Kurir berhasil ditugaskan! Pemberitahuan telah dikirim ke ' . $courier->user->name);
    }

    /**
     * Get formatted shop address snapshot
     */
    public function getShopAddressSnapshot($shop)
    {
        if (!$shop) return null;

        return implode("\n", array_filter([
            $shop->name_store,
            $shop->phone,
            $shop->address,
            $shop->city,
        ]));
    }
}
