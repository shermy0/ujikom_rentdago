<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class KurirQrController extends Controller
{
    /**
     * Show selection page for handover method
     */
    public function showHandoverOptions($shipmentId)
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.dashboard')->with('error', 'Data kurir tidak ditemukan');
        }

        $shipment = Shipment::with(['order.user', 'order.productRental.product', 'order.address'])
            ->where('id', $shipmentId)
            ->where('courier_id', $courier->id)
            ->whereIn('type', [Shipment::TYPE_DELIVERY, Shipment::TYPE_RETURN])
            ->firstOrFail();

        // Check status - must be arrived to complete
        if ($shipment->status !== Shipment::STATUS_ARRIVED) {
            return redirect()->route('kurir.orders')
                ->with('error', 'Pesanan harus dalam status "Sudah Sampai" sebelum diserahkan.');
        }

        return view('kurir.handover.options', compact('shipment'));
    }

    /**
     * Show delivery list for taking photo proof (Legacy/Photo method)
     */
    public function index()
    {
        // Get courier
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.dashboard')->with('error', 'Data kurir tidak ditemukan');
        }

        // Get active deliveries (status: on_the_way atau arrived)
        $activeDeliveries = Shipment::with(['order.user', 'order.productRental.product', 'order.address'])
            ->where('courier_id', $courier->id)
            ->whereIn('type', [Shipment::TYPE_DELIVERY, Shipment::TYPE_RETURN])
            ->whereIn('status', [Shipment::STATUS_ON_THE_WAY, Shipment::STATUS_ARRIVED])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kurir.delivery-photo.index', compact('activeDeliveries'));
    }

    /**
     * Show photo upload page for specific order
     */
    public function showPhotoPage($shipmentId)
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.dashboard')->with('error', 'Data kurir tidak ditemukan');
        }

        $shipment = Shipment::with(['order.user', 'order.productRental.product', 'order.address'])
            ->where('id', $shipmentId)
            ->where('courier_id', $courier->id)
            ->whereIn('type', [Shipment::TYPE_DELIVERY, Shipment::TYPE_RETURN])
            ->firstOrFail();

        // Check status
        if (!in_array($shipment->status, [Shipment::STATUS_ON_THE_WAY, Shipment::STATUS_ARRIVED])) {
            return redirect()->route('kurir.delivery-photo.index')
                ->with('error', 'Status pengiriman tidak valid untuk foto bukti');
        }

        return view('kurir.delivery-photo.take-photo', compact('shipment'));
    }

    /**
     * Verify OTP for handover
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'otp' => 'required|string|size:6',
        ]);

        $courier = Courier::where('user_id', Auth::id())->firstOrFail();
        $shipment = Shipment::with('order.user')
            ->where('id', $request->shipment_id)
            ->where('courier_id', $courier->id)
            ->firstOrFail();

        $otpRecord = \App\Models\Otp::where('user_id', $shipment->order->user_id)
            ->where('code', $request->otp)
            ->where('expired_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'OTP tidak valid atau sudah kadaluarsa.'], 422);
        }

        // Mark OTP as used (by deleting or marking) - for now just delete if it works
        $otpRecord->delete();

        return $this->processHandover($shipment, 'otp');
    }

    /**
     * Verify Customer QR for handover
     */
    public function verifyCustomerQr(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'order_code' => 'required|string',
        ]);

        $courier = Courier::where('user_id', Auth::id())->firstOrFail();
        $shipment = Shipment::with('order')
            ->where('id', $request->shipment_id)
            ->where('courier_id', $courier->id)
            ->firstOrFail();

        if ($shipment->order->order_code !== $request->order_code) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak cocok dengan pesanan ini.'], 422);
        }

        return $this->processHandover($shipment, 'qr');
    }

    /**
     * Complete delivery with photo proof
     */
    public function completeDelivery(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $courier = Courier::where('user_id', Auth::id())->firstOrFail();
        $shipment = Shipment::with('order')
            ->where('id', $request->shipment_id)
            ->where('courier_id', $courier->id)
            ->firstOrFail();

        // Upload photo
        $photoPath = null;
        $photoTimestamp = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $prefix = ($shipment->type == Shipment::TYPE_RETURN) ? 'return_' : 'delivery_';
            $photoName = $prefix . $shipment->order->order_code . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('delivery-proofs', $photoName, 'public');
            $photoTimestamp = now(); // Capture waktu foto diupload
        }

        return $this->processHandover($shipment, 'photo', $photoPath, $photoTimestamp);
    }

    /**
     * Shared logic to process handover
     */
    private function processHandover($shipment, $method, $photoPath = null, $photoTimestamp = null)
    {
        DB::beginTransaction();
        try {
            $order = $shipment->order;

            // HANDLE DELIVERY
            if ($shipment->type === Shipment::TYPE_DELIVERY) {
                $shipment->update([
                    'status' => Shipment::STATUS_DELIVERED,
                    'delivered_at' => now(),
                    'is_tracking_active' => false,
                    'delivery_proof_photo' => $photoPath,
                    'delivery_proof_photo_at' => $photoTimestamp, // Simpan waktu foto
                    'courier_notes' => ($shipment->courier_notes ? $shipment->courier_notes . "\n" : "") . "Diverifikasi via " . strtoupper($method),
                ]);

                // Update order to ONGOING
                $order->update([
                    'status' => Order::STATUS_ONGOING,
                    'paid_at' => $order->paid_at ?? now(),
                ]);

                $successMessage = 'Pesanan berhasil diserahkan! Status kini: Sedang Berlangsung.';
            }


            DB::commit();

            // Notify Seller & Customer (optional logic here)
            $this->sendHandoverNotifications($order, $shipment);

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'redirect' => route('kurir.history') // Or dashboard/orders
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Handover Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send notifications after handover
     */
    private function sendHandoverNotifications($order, $shipment)
    {
        try {
            if ($shipment->type === Shipment::TYPE_DELIVERY) {
                // If photo proof exists, send special notification with photo
                if ($shipment->delivery_proof_photo) {
                    \App\Helpers\CourierNotificationHelper::notifySellerHandover($order, $shipment);
                } else {
                    \App\Helpers\CourierNotificationHelper::notifySellerDeliveryComplete($order);
                }
            } else if ($shipment->type === Shipment::TYPE_RETURN) {
                \App\Helpers\CourierNotificationHelper::notifySellerReturnPickedUp($order);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send handover notifications: ' . $e->getMessage());
        }
    }
}
