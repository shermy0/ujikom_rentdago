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

class CourierAssignmentController extends Controller
{
    /**
     * Assign courier to delivery order
     */
    public function assignCourier(Request $request, $id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return back()->with('error', 'Anda harus membuat toko terlebih dahulu');
        }

        $order = Order::with(['productRental.product', 'user', 'address', 'payment'])
            ->whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        // Validate delivery method
        if ($order->delivery_method !== 'delivery') {
            return back()->with('error', 'Kurir hanya dapat ditugaskan untuk pesanan pengiriman (delivery)');
        }

        // Validate payment status (this is the critical check)
        if ($order->payment?->payment_status !== 'paid') {
            return back()->with('error', 'Pesanan harus dibayar sebelum menugaskan kurir');
        }

        // Validate order status - allow pending, paid, or confirmed
        // (pending is OK if payment_status is paid)
        if (!in_array($order->status, ['pending', 'paid', 'confirmed'])) {
            return back()->with('error', 'Kurir hanya dapat ditugaskan untuk pesanan pending/paid/confirmed. Status saat ini: ' . $order->status);
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
            return back()->with('error', 'Pesanan ini sudah memiliki kurir yang ditugaskan');
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

        // ✅ Send WhatsApp notification to seller/shop owner
        $this->sendSellerNotificationAfterAssignment($shop, $order, $courier, $shipment);

        Log::info('Courier assigned to order', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'courier_id' => $courier->id,
            'shipment_id' => $shipment->id,
            'assigned_by' => Auth::id()
        ]);

        return back()->with('success', 'Kurir berhasil ditugaskan! Notifikasi telah dikirim ke ' . $courier->user->name);
    }

    /**
     * Reassign shipment to another courier after rejection
     */
    public function reassignAfterRejection($shipmentId)
    {
        $shipment = Shipment::with(['order.productRental.product.shop', 'order.user', 'order.address'])
            ->findOrFail($shipmentId);

        $shop = $shipment->order->productRental->product->shop;
        $rejectedCourierIds = $shipment->getRejectedCourierIds();

        Log::info('Attempting to reassign shipment after rejection', [
            'shipment_id' => $shipmentId,
            'order_id' => $shipment->order_id,
            'rejected_by' => $rejectedCourierIds,
        ]);

        // Find available couriers (active, not in rejected list)
        $availableCourier = Courier::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->whereNotIn('id', $rejectedCourierIds)
            ->whereHas('user')
            ->with('user')
            ->first();

        if ($availableCourier) {
            // Assign to new courier - status pending until they accept
            $shipment->update([
                'courier_id' => $availableCourier->id,
                'status' => Shipment::STATUS_PENDING,
                'assigned_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            // Send notification to new courier
            \App\Helpers\CourierNotificationHelper::notifyCourierAssignment(
                $shipment->order,
                $availableCourier,
                $shipment
            );

            Log::info('Shipment reassigned successfully', [
                'shipment_id' => $shipmentId,
                'new_courier_id' => $availableCourier->id,
                'new_courier_name' => $availableCourier->user->name,
            ]);

            return [
                'success' => true,
                'message' => 'Pengiriman berhasil ditugaskan ulang ke ' . $availableCourier->user->name,
                'courier' => $availableCourier,
            ];
        } else {
            // No available courier, set status to rejected (all couriers rejected)
            $shipment->update([
                'status' => Shipment::STATUS_REJECTED,
                'courier_id' => null,
            ]);

            // Notify seller via WhatsApp
            $this->notifySellerNoAvailableCourier($shop, $shipment->order);

            Log::warning('No available courier for reassignment', [
                'shipment_id' => $shipmentId,
                'shop_id' => $shop->id,
                'rejected_by_count' => count($rejectedCourierIds),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak ada kurir yang tersedia. Status pengiriman diubah menjadi ditolak.',
            ];
        }
    }

    /**
     * Send WhatsApp notification to seller after assigning courier
     */
    private function sendSellerNotificationAfterAssignment($shop, $order, $courier, $shipment)
    {
        try {
            $sellerPhone = $shop->user->phone;

            if (!$sellerPhone) {
                Log::warning('Seller has no phone number', ['shop_id' => $shop->id]);
                return;
            }

            // Format phone number (remove leading 0, add 62)
            if (substr($sellerPhone, 0, 1) === '0') {
                $sellerPhone = '62' . substr($sellerPhone, 1);
            }

            $message = "✅ *KURIR BERHASIL DITUGASKAN*\n\n";
            $message .= "Halo *{$shop->user->name}*,\n\n";
            $message .= "Anda telah berhasil menugaskan kurir untuk pesanan berikut:\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "📞 Telepon Customer: *{$order->user->phone}*\n";
            $message .= "💰 Total: *Rp " . number_format($order->payment?->total_amount ?? 0, 0, ',', '.') . "*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n\n";

            $message .= "*INFORMASI KURIR*\n";
            $message .= "🚚 Nama Kurir: *{$courier->user->name}*\n";
            $message .= "📱 Telepon Kurir: *{$courier->user->phone}*\n";
            $message .= "🔢 ID Kurir: #{$courier->id}\n\n";

            if ($shipment->courier_notes) {
                $message .= "📝 *Catatan untuk Kurir:*\n";
                $message .= $shipment->courier_notes . "\n\n";
            }

            $message .= "📅 *Jadwal Pengiriman:*\n";
            $message .= "Mulai: " . Carbon::parse($order->start_time)->format('d/m/Y H:i') . "\n";
            if ($order->end_time) {
                $message .= "Selesai: " . Carbon::parse($order->end_time)->format('d/m/Y H:i') . "\n";
            }
            $message .= "\n";

            $message .= "Notifikasi telah dikirim ke kurir *{$courier->user->name}*.\n";
            $message .= "Kurir akan segera memproses pengiriman pesanan ini.\n\n";
            $message .= "Anda dapat memantau status pengiriman di dashboard seller.\n\n";
            $message .= "Terima kasih! 🙏";

            kirimWa($sellerPhone, $message);

            Log::info('Seller notification sent after courier assignment', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'courier_id' => $courier->id,
                'phone' => $sellerPhone
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send seller notification after courier assignment', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify seller when no courier is available
     */
    private function notifySellerNoAvailableCourier($shop, $order)
    {
        try {
            $sellerPhone = $shop->user->phone;

            // Format phone number
            if (substr($sellerPhone, 0, 1) === '0') {
                $sellerPhone = '62' . substr($sellerPhone, 1);
            }

            // Get the courier who rejected
            $courierName = 'Kurir';
            if ($order->deliveryShipment && !empty($order->deliveryShipment->rejected_by)) {
                $rejectedIds = $order->deliveryShipment->rejected_by;
                $lastRejectedId = end($rejectedIds);
                $courier = Courier::with('user')->find($lastRejectedId);
                if ($courier && $courier->user) {
                    $courierName = $courier->user->name;
                }
            }

            $message = "⚠️ *PENGIRIMAN DITOLAK*\n\n";
            $message .= "Kurir *{$courierName}* telah menolak pesanan ini.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "Silakan cek dashboard untuk menugaskan kurir lain.";

            kirimWa($sellerPhone, $message);

            Log::info('Seller notified about courier rejection', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'phone' => $sellerPhone,
                'rejected_by' => $courierName
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller about courier rejection', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get formatted shop address snapshot
     */
    private function getShopAddressSnapshot($shop)
    {
        if (!$shop) return null;

        return implode("\n", array_filter([
            $shop->name_store,
            $shop->phone,
            $shop->address,
            $shop->city,
        ]));
    }

    /**
     * Manual reassignment of courier for rejected/pending shipments
     */
    public function manualReassignCourier(Request $request, $id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return back()->with('error', 'Anda harus membuat toko terlebih dahulu');
        }

        $order = Order::with(['productRental.product', 'user', 'address', 'deliveryShipment'])
            ->whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        // Validate delivery method
        if ($order->delivery_method !== 'delivery') {
            return back()->with('error', 'Kurir hanya dapat ditugaskan untuk pesanan pengiriman (delivery)');
        }

        // Check if old shipment exists
        $oldShipment = $order->deliveryShipment;
        if (!$oldShipment) {
            return back()->with('error', 'Tidak ada data pengiriman ditemukan untuk pesanan ini');
        }

        // Validate shipment status - must be pending or rejected
        if (!in_array($oldShipment->status, [Shipment::STATUS_PENDING, Shipment::STATUS_REJECTED])) {
            return back()->with('error', 'Hanya dapat menugaskan ulang kurir untuk pengiriman pending atau rejected. Status saat ini: ' . $oldShipment->status);
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

        // Get rejected courier IDs from old shipment to pass to new one
        $rejectedCourierIds = $oldShipment->getRejectedCourierIds();

        // Create NEW shipment instead of updating the old one
        // This preserves the rejection history in the old shipment
        $newShipment = Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'type' => Shipment::TYPE_DELIVERY,
            'status' => Shipment::STATUS_PENDING,
            'pickup_address_snapshot' => $oldShipment->pickup_address_snapshot,  // Copy from old
            'delivery_address_snapshot' => $oldShipment->delivery_address_snapshot,  // Copy from old
            'courier_notes' => $request->courier_notes,
            'assigned_at' => now(),
            'rejected_by' => [],  // Clear rejection list to allow reassigning to previously rejected couriers
        ]);

        // Update order status to confirmed if it's still paid
        if ($order->status === 'paid') {
            $order->update(['status' => Order::STATUS_CONFIRMED]);
        }

        // Send WhatsApp notification to courier
        \App\Helpers\CourierNotificationHelper::notifyCourierAssignment($order, $courier, $newShipment);

        // Send WhatsApp notification to seller
        $this->sendSellerNotificationAfterReassignment($shop, $order, $courier, $newShipment);

        Log::info('New shipment created for courier reassignment after rejection', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'old_shipment_id' => $oldShipment->id,
            'old_shipment_status' => $oldShipment->status,
            'new_shipment_id' => $newShipment->id,
            'courier_id' => $courier->id,
            'reassigned_by' => Auth::id(),
            'rejected_by_count' => count($rejectedCourierIds),
        ]);

        return back()->with('success', 'Kurir berhasil ditugaskan ulang! Notifikasi telah dikirim ke ' . $courier->user->name);
    }

    /**
     * Send WhatsApp notification to seller after manual reassignment
     */
    private function sendSellerNotificationAfterReassignment($shop, $order, $courier, $shipment)
    {
        try {
            $sellerPhone = $shop->user->phone;

            if (!$sellerPhone) {
                Log::warning('Seller has no phone number', ['shop_id' => $shop->id]);
                return;
            }

            // Format phone number (remove leading 0, add 62)
            if (substr($sellerPhone, 0, 1) === '0') {
                $sellerPhone = '62' . substr($sellerPhone, 1);
            }

            $rejectedCount = count($shipment->rejected_by ?? []);

            $message = "✅ *KURIR BERHASIL DITUGASKAN ULANG*\n\n";
            $message .= "Halo *{$shop->user->name}*,\n\n";
            $message .= "Anda telah berhasil menugaskan ulang kurir untuk pesanan yang sebelumnya ditolak:\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "💰 Total: *Rp " . number_format($order->payment?->total_amount ?? 0, 0, ',', '.') . "*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━\n\n";

            $message .= "*INFORMASI KURIR BARU*\n";
            $message .= "🚚 Nama Kurir: *{$courier->user->name}*\n";
            $message .= "📱 Telepon Kurir: *{$courier->user->phone}*\n\n";

            if ($rejectedCount > 0) {
                $message .= "⚠️ *Catatan:* {$rejectedCount} kurir sebelumnya telah menolak pesanan ini.\n\n";
            }

            $message .= "Notifikasi telah dikirim ke kurir *{$courier->user->name}*.\n";
            $message .= "Kurir akan segera memproses pengiriman pesanan ini.\n\n";
            $message .= "Terima kasih! 🙏";

            kirimWa($sellerPhone, $message);

            Log::info('Seller notification sent after manual reassignment', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'courier_id' => $courier->id,
                'phone' => $sellerPhone
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send seller notification after manual reassignment', [
                'shop_id' => $shop->id,
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Return logic has been removed as per request.
}
