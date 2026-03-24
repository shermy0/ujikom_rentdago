<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductRental;
use App\Models\UserAddress;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Halaman daftar order user
     */
    public function index()
    {
        $orders = Order::with(['productRental.product', 'productRental.product.images', 'payment'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('frontend.order.index', compact('orders'))->with('title', 'Pesanan');
    }

    /**
     * Proses pembuatan order
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'product_rental_id' => 'required|exists:product_rentals,id',
            'start_time'        => 'required|date',
            'delivery_method'   => 'nullable|in:pickup,delivery',
            'user_address_id'   => 'nullable|exists:user_addresses,id',
            'voucher_id'        => 'nullable|exists:vouchers,id',
        ]);

        $startTime = Carbon::parse($request->start_time);
        $now = now()->startOfMinute();

        if ($startTime->lt($now)) {
            return back()
                ->with('invalid_start_time', true)
                ->withInput();
        }

        $rental = ProductRental::findOrFail($request->product_rental_id);
        $endTime = $startTime->copy()->addHours($rental->cycle_value);

        // 🔴 CEK BENTROK WAKTU (PER PAKET / PRODUCT_RENTALS)
        $blockedStatuses = ['pending', 'confirmed', 'ongoing'];

        $isConflict = Order::where('product_rental_id', $rental->id)
            ->whereIn('status', $blockedStatuses)
            ->where(function ($q) use ($startTime, $endTime) {
                // overlap waktu (standar booking)
                $q->where('start_time', '<', $endTime)
                    ->where(function ($qq) use ($startTime) {
                        $qq->whereNull('end_time')
                            ->orWhere('end_time', '>', $startTime);
                    });
            })
            ->exists();

        if ($isConflict) {
            return back()
                ->with('rent_conflict', true)
                ->withInput();
        }

        $deliveryMethod = $request->delivery_method ?? $rental->is_delivery;

        // 🔵 VALIDASI ALAMAT JIKA DELIVERY
        $address = null;

        if ($deliveryMethod === 'delivery') {
            $request->validate([
                'user_address_id' => 'required',
            ]);

            $address = UserAddress::where('id', $request->user_address_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        // 🔵 SNAPSHOT ALAMAT
        $addressSnapshot = null;
        if ($address) {
            $addressSnapshot =
                "{$address->receiver_name} ({$address->receiver_phone})\n" .
                "{$address->address}\n" .
                ($address->notes ? "Catatan: {$address->notes}" : '');
        }

        // 🔵 HITUNG TOTAL DENGAN VOUCHER
        $originalAmount = $rental->price;
        $discountAmount = 0;
        $voucher = null;

        if ($request->voucher_id) {
            $voucher = Voucher::findOrFail($request->voucher_id);

            // Validasi voucher bisa digunakan
            if ($voucher->canBeUsed(Auth::id(), $originalAmount)) {
                $discountAmount = $voucher->calculateDiscount($originalAmount);
            } else {
                return back()
                    ->with('error', 'Voucher tidak dapat digunakan')
                    ->withInput();
            }
        }

        $finalAmount = $originalAmount - $discountAmount;

        // 🔵 CREATE ORDER
        $order = Order::create([
            'user_id'                   => Auth::id(),
            'product_rental_id'         => $rental->id,
            'user_address_id'           => $address?->id,
            'delivery_address_snapshot' => $addressSnapshot,
            'order_code'                => 'ORD-' . strtoupper(Str::random(10)),
            'start_time'                => $startTime,
            'end_time'                  => $endTime,
            'status'                    => 'pending',
            'delivery_method'           => $deliveryMethod,
        ]);

        // 🔵 CREATE PAYMENT
        Payment::create([
            'order_id'       => $order->id,
            'total_amount'   => $finalAmount,
            'payment_status' => 'unpaid',
        ]);

        // 🔵 CATAT PENGGUNAAN VOUCHER
        if ($voucher) {
            VoucherUsage::create([
                'voucher_id'      => $voucher->id,
                'user_id'         => Auth::id(),
                'order_id'        => $order->id,
                'discount_amount' => $discountAmount,
            ]);
        }

        return redirect()->route('customer.order.payment', $order->id);
    }

    /**
     * Halaman detail order & pembayaran
     */
    public function show($id)
    {
        $order = Order::with([
            'productRental.product.images',
            'productRental.product.shop',
            'deliveryShipment.courier.user',
            'address',
            'payment',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // ========================================
        // PREPARE MAP DATA (Pickup & Delivery)
        // ========================================
        $mapData = null;
        $deliveryMethod = $order->delivery_method ?? $order->productRental->is_delivery;

        if ($order->status !== 'cancelled' && $order->status !== 'pending') {
            $shop = $order->productRental->product->shop;
            $shipment = $order->deliveryShipment;
            $userAddress = $order->address;

            if ($shop && $shop->latitude && $shop->longitude) {
                // Determine destination and current actor positions
                $destLat = $userAddress?->latitude ?? $shop->latitude;
                $destLng = $userAddress?->longitude ?? $shop->longitude;

                // For pickup: Shop is destination, Customer is actor
                if ($deliveryMethod === 'pickup') {
                    $actorLat = ($shipment && $shipment->is_tracking_active) ? ($shipment->last_lat ?? $userAddress->latitude ?? $shop->latitude) : ($userAddress->latitude ?? $shop->latitude);
                    $actorLng = ($shipment && $shipment->is_tracking_active) ? ($shipment->last_lng ?? $userAddress->longitude ?? $shop->longitude) : ($userAddress->longitude ?? $shop->longitude);

                    $mapData = [
                        'shop' => [
                            'name' => $shop->name_store,
                            'lat' => $shop->latitude,
                            'lng' => $shop->longitude,
                            'address' => $shop->address_store,
                            'image' => $shop->logo ? asset('storage/' . $shop->logo) : 'https://ui-avatars.com/api/?name=' . urlencode($shop->name_store) . '&background=0D8ABC&color=fff'
                        ],
                        'customer' => [
                            'name' => Auth::user()->name,
                            'lat' => $actorLat,
                            'lng' => $actorLng,
                            'address' => $userAddress->address ?? 'Lokasi Anda',
                            'image' => Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random'
                        ],
                        'shipment' => $shipment ? [
                            'is_tracking_active' => $shipment->is_tracking_active,
                            'status' => $shipment->status,
                        ] : null,
                        'delivery_method' => 'pickup'
                    ];
                }
                // For delivery: Customer is destination, Courier is actor
                else if ($deliveryMethod === 'delivery' && $shipment) {
                    $actorLat = $shipment->last_lat ?? $shop->latitude;
                    $actorLng = $shipment->last_lng ?? $shop->longitude;

                    $mapData = [
                        'customer' => [
                            'name' => Auth::user()->name,
                            'lat' => $destLat,
                            'lng' => $destLng,
                            'address' => $userAddress->address ?? 'Alamat Anda',
                            'image' => Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random'
                        ],
                        'courier' => [
                            'name' => $shipment->courier->user->name ?? 'Kurir',
                            'lat' => $actorLat,
                            'lng' => $actorLng,
                            'image' => ($shipment->courier && $shipment->courier->user->avatar) ? asset('storage/' . $shipment->courier->user->avatar) : 'https://ui-avatars.com/api/?name=Kurir&background=random'
                        ],
                        'shipment' => [
                            'id' => $shipment->id,
                            'is_tracking_active' => $shipment->is_tracking_active,
                            'status' => $shipment->status,
                        ],
                        'delivery_method' => 'delivery'
                    ];
                }
            }
        }

        // ========================================
        // URUTAN PENGECEKAN SANGAT PENTING!
        // ========================================

        // ✅ PRIORITAS 1: CEK CANCELLED DULU (apapun payment_status-nya)
        if ($order->status === 'cancelled') {
            return view('frontend.order.detail', compact('order', 'mapData'));
        }

        // ✅ PRIORITAS 2: Jika sudah dibayar (paid), tampilkan detail
        if ($order->payment?->payment_status === 'paid') {
            return view('frontend.order.detail', compact('order', 'mapData'));
        }

        // ✅ PRIORITAS 3: Jika belum bayar DAN masih pending, tampilkan payment
        if ($order->payment?->payment_status === 'unpaid' && $order->status === 'pending') {
            $snapToken = $this->generateSnapToken($order);
            return view('frontend.order.payment', compact('order', 'snapToken'));
        }

        // ✅ PRIORITAS 4: Default fallback untuk kondisi edge case
        return view('frontend.order.detail', compact('order', 'mapData'))->with('title', 'Detail Pesanan');
    }

    /**
     * Generate Midtrans Snap Token
     */
    private function generateSnapToken($order)
    {
        // Log Midtrans configuration status (without exposing keys)
        Log::info('Attempting to generate Midtrans token', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'has_server_key' => !empty(config('midtrans.server_key')),
            'has_client_key' => !empty(config('midtrans.client_key')),
            'is_production' => config('midtrans.is_production'),
        ]);

        // Load shop relationship to get merchant name
        $order->load('productRental.product.shop');

        // Add unique timestamp suffix to order_id to allow retry payments
        // Midtrans requires globally unique order_id for each API call
        $uniqueOrderId = $order->order_code . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $uniqueOrderId,
                'gross_amount' => $order->payment?->total_amount ?? 0,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id'            => $order->productRental->id,
                    'price'         => $order->payment?->total_amount ?? 0,
                    'quantity'      => 1,
                    'name'          => $order->productRental->product->name . ' (' . $order->productRental->cycle_value . ' Jam)',
                    'merchant_name' => $order->productRental->product->shop->name_store ?? 'RentDago',
                ]
            ],
        ];

        try {
            Log::info('Midtrans request params prepared', [
                'order_code' => $order->order_code,
                'amount' => $order->payment?->total_amount ?? 0,
            ]);

            $snapToken = Snap::getSnapToken($params);

            Log::info('Midtrans token generated successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Failed to generate Midtrans token', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function regenerateToken($id)
    {
        $order = Order::with(['productRental.product', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        Log::info('Token regeneration requested', [
            'order_id'  => $order->id,
            'order_code'=> $order->order_code,
            'user_id'   => Auth::id(),
        ]);

        // Cek apakah order masih bisa dibayar
        if ($order->payment?->payment_status === 'paid') {
            Log::warning('Token regeneration rejected - order already paid', [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order sudah dibayar'
            ], 400);
        }

        if ($order->status === 'cancelled') {
            Log::warning('Token regeneration rejected - order cancelled', [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order sudah dibatalkan'
            ], 400);
        }

        // Generate token baru
        $snapToken = $this->generateSnapToken($order);

        if (!$snapToken) {
            Log::error('Token regeneration failed - generateSnapToken returned null', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate token pembayaran. Silakan periksa konfigurasi Midtrans atau coba lagi nanti.'
            ], 500);
        }

        Log::info('Token regenerated successfully', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
        ]);

        return response()->json([
            'success' => true,
            'snap_token' => $snapToken
        ]);
    }

    public function midtransCallback(Request $request)
    {
        // =====================================================
        // 1. Ambil RAW payload
        // =====================================================
        $payload = $request->getContent();
        $notification = json_decode($payload);

        if (!$notification || !isset(
            $notification->order_id,
            $notification->status_code,
            $notification->gross_amount,
            $notification->signature_key,
            $notification->transaction_status
        )) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // =====================================================
        // 2. Verifikasi Signature Midtrans
        // =====================================================
        $serverKey = config('midtrans.server_key');
        $signatureKey = hash(
            'sha512',
            $notification->order_id .
                $notification->status_code .
                $notification->gross_amount .
                $serverKey
        );

        if ($signatureKey !== $notification->signature_key) {
            Log::warning('Midtrans signature mismatch', [
                'expected' => $signatureKey,
                'received' => $notification->signature_key,
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // =====================================================
        // 3. Common Variables
        // =====================================================
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $now = now();

        // =====================================================
        // 🔥 4. HANDLE PENALTY (ORDER_RETURN)
        // =====================================================
        if (Str::startsWith($notification->order_id, 'PENALTY-')) {

            $penalty = OrderReturn::with('order')
                ->where('midtrans_order_id', $notification->order_id)
                ->first();

            if (!$penalty) {
                Log::error('Penalty not found', [
                    'midtrans_order_id' => $notification->order_id
                ]);
                return response()->json(['message' => 'Penalty not found'], 404);
            }

            // Guard multiple callback
            if ($penalty->payment_status === 'paid') {
                return response()->json(['message' => 'Penalty already processed']);
            }

            if (
                $transactionStatus === 'settlement' ||
                ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            ) {
                // Update penalty
                $penalty->update([
                    'payment_status' => 'paid',
                    'paid_at' => $now,
                ]);

                // 🔥 Order baru boleh completed SETELAH denda lunas
                $penalty->order->update([
                    'status' => 'completed'
                ]);

                // 🔥 NEW: Trigger Penalty Paid Notification
                \App\Helpers\CustomerNotificationHelper::notifyPenaltyPaid($penalty->order);
            }

            return response()->json(['message' => 'Penalty callback processed']);
        }

        // =====================================================
        // 5. HANDLE ORDER NORMAL
        // =====================================================
        $order = Order::where('order_code', $notification->order_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Guard double callback
        if ($order->payment?->payment_status === 'paid') {
            return response()->json(['message' => 'Already processed']);
        }

        if (
            $transactionStatus === 'settlement' ||
            ($transactionStatus === 'capture' && $fraudStatus === 'accept')
        ) {
            $rental = $order->productRental;

            // Tentukan status order
            if (in_array($order->delivery_method, ['delivery', 'pickup'])) {
                $newStatus = 'confirmed';
                $startTime = $now;
                $endTime = $now->copy()->addHours($rental->cycle_value);
            } else {
                $scheduledStart = Carbon::parse($order->start_time);

                if ($now->gte($scheduledStart)) {
                    $newStatus = 'ongoing';
                    $startTime = $now;
                    $endTime = $now->copy()->addHours($rental->cycle_value);
                } else {
                    $newStatus = 'confirmed';
                    $startTime = $scheduledStart;
                    $endTime = null;
                }
            }

            $order->update([
                'status'     => $newStatus,
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ]);

            // Update payment record
            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_status' => 'paid',
                    'paid_at'        => $now,
                ]
            );

            // Generate QR Code
            if ($newStatus === 'confirmed' && !$order->qr_code) {
                try {
                    $qrCodePath = $this->generateQrCode($order);
                    $order->update(['qr_code' => $qrCodePath]);
                } catch (\Exception $e) {
                    Log::error('QR Code generation failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Kirim receipt WA
            $this->sendConfirmedPaymentReceipt($order);

            // 🔥 Kirim notifikasi In-App ke Customer (Bell) - HANDLED BY OrderObserver
            // \App\Helpers\CustomerNotificationHelper::notifyPaymentSuccess($order);

            // 🔥 Kirim notifikasi ke Seller
            $this->sendSellerOrderPaidNotification($order);

            // 🔥 GENERATE OTP untuk Handover (valid 24 jam)
            try {
                \App\Models\Otp::create([
                    'user_id' => $order->user_id,
                    'phone'   => $order->user->phone,
                    'code'    => sprintf("%06d", mt_rand(1, 999999)),
                    'expired_at' => now()->addHours(24),
                ]);
            } catch (\Exception $e) {
                Log::error('OTP generation failed after payment success: ' . $e->getMessage());
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update([
                'status'             => 'cancelled',
                'is_read_by_seller'  => false
            ]);
            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                ['payment_status' => 'unpaid']
            );
        } elseif ($transactionStatus === 'pending') {
            $order->update([
                'status'            => Order::STATUS_PENDING,
                'is_read_by_seller' => false
            ]);
            // payment_status stays unpaid, no change needed
        }

        return response()->json(['message' => 'Callback processed']);
    }

    private function generateQrCode($order)
    {
        $qrData = $order->order_code;

        $qrCodePath = 'qrcodes/' . $order->order_code . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        if (!file_exists(storage_path('app/public/qrcodes'))) {
            mkdir(storage_path('app/public/qrcodes'), 0755, true);
        }

        QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrData, $fullPath);

        return $qrCodePath;
    }

    public function finish(Request $request)
    {
        $orderCode = $request->order_id;

        $order = Order::with(['productRental.product.shop'])
            ->where('order_code', $orderCode)
            ->first();

        if (!$order) {
            return redirect()
                ->route('customer.order.index')
                ->with('error', 'Order tidak ditemukan');
        }

        // ==========================
        // PASTIKAN STATUS PEMBAYARAN
        // ==========================
        if ($order->payment?->payment_status !== 'paid') {
            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                ]
            );
            $order->update([
                'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
            ]);
        }

        // ==========================
        // GENERATE QR CODE (ONCE)
        // ==========================
        if (
            !$order->qr_code &&
            in_array($order->status, ['confirmed', 'ongoing'])
        ) {
            try {
                $qrCodePath = $this->generateQrCode($order);
                $order->update(['qr_code' => $qrCodePath]);
            } catch (\Throwable $e) {
                Log::error('QR generation failed on finish()', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Kirim receipt confirmed payment ke WhatsApp
        $this->sendConfirmedPaymentReceipt($order);

        // 🔥 Kirim notifikasi In-App ke Customer (Bell) - HANDLED BY OrderObserver
        // \App\Helpers\CustomerNotificationHelper::notifyPaymentSuccess($order);

        // 🔥 Kirim notifikasi ke Seller
        $this->sendSellerOrderPaidNotification($order);

        return redirect()
            ->route('customer.order.show', $order->id)
            ->with('success', 'Pembayaran berhasil diproses!');
    }

    /**
     * Batalkan order
     */
    /**
     * Batalkan order
     */
    public function cancel($id)
    {
        $order = Order::with('payment')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->whereHas('payment', fn($q) => $q->where('payment_status', 'unpaid'))
            ->firstOrFail();

        // ✅ Set is_read_by_seller = false agar muncul di notifikasi seller
        $order->update([
            'status'            => 'cancelled',
            'is_read_by_seller' => false
        ]);

        // Stop tracking if active
        $shipment = \App\Models\Shipment::where('order_id', $id)
            ->where('is_tracking_active', true)
            ->first();

        if ($shipment) {
            $shipment->update([
                'is_tracking_active' => false,
                'status' => \App\Models\Shipment::STATUS_FAILED
            ]);
        }

        return redirect()->back()->with('success', 'Order berhasil dibatalkan');
    }

    public function startRental($id)
    {
        $order = Order::with(['productRental', 'payment'])->findOrFail($id);

        // Stop kalau sudah dimulai
        if ($order->start_time) {
            return redirect()->back()
                ->with('error', 'Sewa sudah dimulai sebelumnya.');
        }

        if (
            $order->status !== 'confirmed' ||
            $order->payment?->payment_status !== 'paid'
        ) {
            return redirect()->back()
                ->with('error', 'Order belum siap untuk dimulai.');
        }

        $rental = $order->productRental;
        $now = now();
        $endTime = $now->copy()->addHours($rental->cycle_value);

        $order->update([
            'status' => Order::STATUS_ONGOING,
            'start_time' => $now,
            'end_time' => $endTime,
        ]);

        return redirect()->back()
            ->with('success', 'Sewa berhasil dimulai!');
    }





    /**
     * ✅ Kirim notifikasi ke Seller bahwa pesanan sudah dibayar
     */
    private function sendSellerOrderPaidNotification($order)
    {
        try {
            // Ensure relations are loaded
            if (!$order->relationLoaded('productRental.product.shop.user')) {
                $order->load(['productRental.product.shop.user']);
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            if (!$seller || !$seller->phone) {
                Log::warning('Seller info not found for order', ['order_id' => $order->id]);
                return;
            }

            $phone = $seller->phone;
            // Format phone number
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $message = "*🔔 PESANAN BARU SUDAH DIBAYAR*\n\n";
            $message .= "Halo *{$shop->name_store}*,\n\n";
            $message .= "Ada pesanan baru yang sudah dibayar oleh customer.\n\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━\n";
            $message .= " Kode Order: *{$order->order_code}*\n";
            $message .= " Produk: *{$order->productRental->product->name}*\n";
            $message .= " Durasi: *{$order->productRental->cycle_value} Jam*\n";
            $message .= "📅 Waktu Mulai: *" . Carbon::parse($order->start_time)->format('d/m/Y H:i') . "*\n";
            $message .= "━━━━━━━━━━━━━━\n\n";
            $message .= "⚠ *Mohon segera siapkan pesanan!*\n";
            $message .= "Cek detail pesanan di aplikasi seller.\n\n";
            $message .= "Semangat berjualan! 💪";

            if (function_exists('kirimwa')) {
                kirimwa($phone, $message);
            }

            Log::info('Seller paid notification sent', [
                'order_id' => $order->id,
                'seller_phone' => $phone
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send seller paid notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ Kirim receipt confirmed payment + QR Code ke WhatsApp
     */
    /**
     *  Kirim receipt confirmed payment + QR Code ke WhatsApp (SATU PESAN)
     */
    private function sendConfirmedPaymentReceipt($order)
    {
        logger($order);
        try {
            $order->load(['user', 'productRental.product.shop', 'payment']);

            $phone = $order->user->phone;

            // Format nomor telepon (hapus 0 di depan, tambah 62)
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            // Buat pesan lengkap
            $message = "* PEMBAYARAN BERHASIL*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pembayaran Anda telah berhasil diproses!\n\n";
            $message .= "━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "🏪 Toko: *{$order->productRental->product->shop->name_store}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= " Durasi: *{$order->productRental->cycle_value} Jam*\n";
            $message .= "💰 Total: *Rp " . number_format($order->payment?->total_amount ?? 0, 0, ',', '.') . "*\n";
            $message .= "🚚 Metode: *" . ucfirst($order->delivery_method) . "*\n";
            $message .= "📅 Waktu Mulai: *" . Carbon::parse($order->start_time)->format('d/m/Y H:i') . "*\n";

            if ($order->end_time) {
                $message .= " Waktu Selesai: *" . Carbon::parse($order->end_time)->format('d/m/Y H:i') . "*\n";
            }

            $message .= "━━━━━━━━━━━━━━\n\n";

            if ($order->delivery_method === 'delivery' && $order->delivery_address_snapshot) {
                $message .= "📍 *Alamat Pengiriman:*\n";
                $message .= $order->delivery_address_snapshot . "\n\n";
            }

            $message .= " *QR CODE*\n";
            $message .= "Tunjukkan QR Code ini kepada penjual saat pengambilan atau pengembalian barang.\n\n";
            $message .= "Detail pesanan:\n";
            $message .= route('customer.order.show', $order->id) . "\n\n";
            $message .= "Terima kasih telah berbelanja! 🙏";
            logger($message);

            // ✅ Ambil QR Code dari database (kolom qr_code di tabel orders)
            if (!empty($order->qr_code)) {
                // Path lengkap ke file QR code di storage
                $qrCodePath = config('app.url') . Storage::url($order->qr_code);
                logger($qrCodePath);

                // Cek apakah file fisik ada
                if ($order->qr_code) {
                    //  Kirim sebagai media dengan caption (SATU PESAN)
                    $result = kirimWa($phone, $message, $qrCodePath);

                    Log::info('Confirmed payment receipt with QR Code sent', [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'phone' => $phone,
                        'has_qr' => true,
                        'qr_path' => $order->qr_code,
                        'send_result' => $result
                    ]);
                } else {
                    // File QR tidak ditemukan di storage
                    Log::warning('QR Code file not found in storage', [
                        'order_id' => $order->id,
                        'qr_path_db' => $order->qr_code,
                        'full_path' => $qrCodePath
                    ]);

                    // Kirim text saja tanpa QR
                    $result = kirimWa($phone, $message);

                    Log::info('Sent text only (QR file missing)', [
                        'order_id' => $order->id,
                        'send_result' => $result
                    ]);
                }
            } else {
                // QR Code belum tersimpan di database
                Log::info('QR Code not yet generated in database', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);

                // Kirim text saja
                $result = kirimWa($phone, $message);

                Log::info('Confirmed payment receipt sent (no QR yet)', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'phone' => $phone,
                    'send_result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send confirmed payment receipt', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get tracking status for customer (polling)
     */
    public function getTrackingStatus($id)
    {
        $order = Order::with(['deliveryShipment.courier.user', 'productRental.product.shop'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $shipment = $order->deliveryShipment;

        if (!$shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found'], 404);
        }

        $data = [
            'status' => 'success',
            'order_status' => $order->status,
            'shipment_status' => $shipment->status,
            'is_tracking_active' => $shipment->is_tracking_active,
            'last_lat' => $shipment->last_lat,
            'last_lng' => $shipment->last_lng,
            'updated_at' => $shipment->updated_at->toIso8601String()
        ];

        // For pickup orders, include distance to shop
        if ($order->delivery_method === 'pickup') {
            $shop = $order->productRental->product->shop;

            if ($shop && $shop->latitude && $shop->longitude && $shipment->last_lat && $shipment->last_lng) {
                $distance = $shipment->calculateDistanceTo($shop->latitude, $shop->longitude);
                $data['distance'] = $distance;
                $data['can_arrive'] = $distance !== null && $distance <= \App\Models\Shipment::ARRIVAL_THRESHOLD;
                $data['threshold'] = \App\Models\Shipment::ARRIVAL_THRESHOLD;
            }
        }

        return response()->json($data);
    }
    // Return feature removed as per request.
}
