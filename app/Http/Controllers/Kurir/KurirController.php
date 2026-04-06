<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KurirController extends Controller
{
    /**
     * Display courier dashboard
     */
    public function index()
    {
        // Get courier record for logged-in user
        $courier = Courier::where('user_id', Auth::id())->first();

        $stats = [
            'perlu_diambil' => 0,
            'sedang_dikirim' => 0,
        ];

        if ($courier) {
            // Count shipments by status - include pending (awaiting acceptance)
            $stats['perlu_diambil'] = Shipment::where('courier_id', $courier->id)
                ->where('type', Shipment::TYPE_DELIVERY)
                ->whereIn('status', [Shipment::STATUS_PENDING, Shipment::STATUS_ASSIGNED])
                ->count();

            $stats['sedang_dikirim'] = Shipment::where('courier_id', $courier->id)
                ->where('type', Shipment::TYPE_DELIVERY)
                ->whereIn('status', [
                    Shipment::STATUS_PICKED_UP,
                    Shipment::STATUS_ON_THE_WAY
                ])
                ->count();

            // Get recent activity (latest shipment update)
            $recentShipment = Shipment::with('order.user')
                ->where('courier_id', $courier->id)
                ->where('type', Shipment::TYPE_DELIVERY)
                ->orderBy('updated_at', 'desc')
                ->first();
        } else {
            $recentShipment = null;
        }

        return view('kurir.index', compact('stats', 'recentShipment'))->with('title', 'Beranda');
    }

    /**
     * Display courier orders list
     */
    public function orders()
    {
        // Get courier record for logged-in user
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return view('kurir.orders', ['orders' => collect([])]);
        }

        // Get orders through shipments
        // 1. Specifically assigned to this courier OR
        // 2. Unassigned (pool) but from the same shop and not rejected by this courier before
        $orders = Order::with([
            'user',
            'productRental.product.images',
            'productRental.product.shop',
            'shipments' => function ($query) use ($courier) {
                // Ensure we see both delivery and return shipments related to this courier or the pool
                $query->where('type', Shipment::TYPE_DELIVERY);
            }
        ])
            ->whereHas('shipments', function ($query) use ($courier) {
                $query->whereIn('type', [Shipment::TYPE_DELIVERY]) // REMOVE RETURN
                    ->where(function ($q) use ($courier) {
                        // Priority 1: Specifically assigned to this courier
                        $q->where('courier_id', $courier->id)
                            ->whereIn('status', [
                                Shipment::STATUS_PENDING,
                                Shipment::STATUS_ASSIGNED,
                                Shipment::STATUS_PICKED_UP,
                                Shipment::STATUS_ON_THE_WAY,
                                Shipment::STATUS_ARRIVED
                            ]);
                    })
                    ->orWhere(function ($q) use ($courier) {
                        // Priority 2: Pool (unassigned) from the same shop
                        $q->whereNull('courier_id')
                            ->where('status', Shipment::STATUS_PENDING)
                            ->whereHas('order.productRental.product.shop', function ($sq) use ($courier) {
                                $sq->where('id', $courier->shop_id);
                            });
                    });
            })
            ->get()
            ->filter(function ($order) use ($courier) {
                // Get the latest relevant shipment (delivery or return)
                $shipment = $order->shipments
                    ->where('type', Shipment::TYPE_DELIVERY)
                    ->sortByDesc('created_at')
                    ->first();

                if (!$shipment) return false;

                // ALWAYS show if assigned to this courier (overrides rejection history)
                if ($shipment->courier_id === $courier->id) {
                    return true;
                }

                // If not assigned to me (pool order), hide if I rejected it
                return !$shipment->hasBeenRejectedBy($courier->id);
            })
            ->values();

        return view('kurir.orders', compact('orders'))->with('title', 'Pesanan');
    }

    /**
     * Display scan QR page
     */
    public function scan()
    {
        return view('kurir.scan')->with('title', 'Scan');
    }

    /**
     * Display delivery history
     */
    public function history()
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return view('kurir.history', [
                'shipments' => collect([]),
                'todayCount' => 0,
                'weekCount' => 0,
                'monthCount' => 0
            ]);
        }

        // Get completed deliveries (delivered, failed, returned, or rejected)
        // Get completed deliveries (delivered, failed, returned, or rejected)
        // Fetch SHIPMENTS directly to allow multiple entries per order (e.g. Rejected then Delivered)
        $shipments = Shipment::with([
            'order.user',
            'order.productRental.product.images',
            'order.productRental.product.shop',
            'order.address'
        ])
            ->whereIn('type', [Shipment::TYPE_DELIVERY]) // REMOVE RETURN
            ->where(function ($q) use ($courier) {
                // Show shipments assigned to this courier OR rejected by this courier
                $q->where('courier_id', $courier->id)
                    ->orWhereJsonContains('rejected_by', $courier->id);
            })
            ->whereIn('status', [
                Shipment::STATUS_DELIVERED,
                Shipment::STATUS_FAILED,
                Shipment::STATUS_RETURNED,
                Shipment::STATUS_REJECTED
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Calculate statistics (only successful deliveries for stats)
        $now = now();
        $todayCount = $shipments->filter(function ($shipment) use ($now) {
            return $shipment->updated_at->isToday() && $shipment->status === Shipment::STATUS_DELIVERED;
        })->count();

        $weekCount = $shipments->filter(function ($shipment) use ($now) {
            return $shipment->updated_at->isCurrentWeek() && $shipment->status === Shipment::STATUS_DELIVERED;
        })->count();

        $monthCount = $shipments->filter(function ($shipment) use ($now) {
            return $shipment->updated_at->isCurrentMonth() && $shipment->status === Shipment::STATUS_DELIVERED;
        })->count();

        return view('kurir.history', compact('shipments', 'todayCount', 'weekCount', 'monthCount'))->with('title', 'Riwayat');

        $weekCount = $orders->filter(function ($order) use ($now) {
            $deliveredAt = $order->deliveryShipment->updated_at ?? $order->updated_at;
            return $deliveredAt->isCurrentWeek() && $order->deliveryShipment->status === Shipment::STATUS_DELIVERED;
        })->count();

        $monthCount = $orders->filter(function ($order) use ($now) {
            $deliveredAt = $order->deliveryShipment->updated_at ?? $order->updated_at;
            return $deliveredAt->isCurrentMonth() && $order->deliveryShipment->status === Shipment::STATUS_DELIVERED;
        })->count();

        return view('kurir.history', compact('orders', 'todayCount', 'weekCount', 'monthCount'))->with('title', 'Riwayat');
    }

    /**
     * Display courier profile
     */
    public function profile()
    {
        return view('kurir.profile')->with('title', 'Profil');
    }

    /**
     * Show edit profile form
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('kurir.edit-profile', compact('user'))->with('title', 'Ubah Profil');
    }

    /**
     * Update courier profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone,' . $user->id,
        ]);

        try {
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->save();

            return redirect()->route('kurir.profile')
                ->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }

    /**
     * Show change password form
     */
    public function showChangePassword()
    {
        return view('kurir.change-password')->with('title', 'Ubah Password');
    }

    /**
     * Update courier password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai!');
        }

        try {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->route('kurir.profile')
                ->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }

    /**
     * Show delivery map for courier
     */
    public function showMap($orderId)
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.orders')->with('error', 'Kurir tidak ditemukan');
        }

        // Query order melalui shipments, bukan langsung where courier_id
        $order = Order::with([
            'user',
            'productRental.product.shop',
            'address',
            'deliveryShipment' // We might need returnShipment too, but let's filter via shipments
        ])
            ->where('id', $orderId)
            ->whereHas('shipments', function ($query) use ($courier) {
                $query->where('courier_id', $courier->id)
                    ->where('type', Shipment::TYPE_DELIVERY);
            })
            ->firstOrFail();

        // Intelligent Shipment Selection
        // Find the shipment that is currently active or assigned to this courier for this order using the collection
        // We prefer the one that is NOT completed (delivered/returned) if possible.

        $shipments = $order->shipments->where('courier_id', $courier->id)->where('type', Shipment::TYPE_DELIVERY);

        // Priority: On The Way > Picked Up > Assigned > Delivered/Returned
        // But simpler: just get the one that matches the current order phase or the latest one.

        $shipment = $shipments->sortByDesc('updated_at')->first();

        if (!$shipment) {
            return redirect()->route('kurir.orders')->with('error', 'Shipment tidak ditemukan atau bukan milik Anda');
        }

        // Simple map data
        $mapData = [
            'shop' => [
                'name' => $order->productRental->product->shop->name_store ?? 'Toko',
                'lat' => $order->productRental->product->shop->latitude ?? -6.200000,
                'lng' => $order->productRental->product->shop->longitude ?? 106.816666,
            ],
            'customer' => [
                'name' => $order->user->name,
                'address' => $shipment->delivery_address_snapshot ?? 'Alamat tidak tersedia',
                'lat' => $order->address->latitude ?? -6.175110,
                'lng' => $order->address->longitude ?? 106.865039,
            ],
            'shipment' => [
                'id' => $shipment->id,
                'type' => $shipment->type,
                'picked_up_at' => $shipment->picked_up_at,
                'is_tracking_active' => $shipment->is_tracking_active,
                'status' => $shipment->status,
            ]
        ];

        return view('kurir.map', compact('order', 'shipment', 'mapData'))->with('title', 'Map');
    }

    /**
     * Hand over the item to customer
     * POST /courier/hand-over
     */
    public function handOver(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
        ]);

        $courier = Courier::where('user_id', Auth::id())->first();
        if (!$courier) return response()->json(['message' => 'Unauthorized'], 401);

        try {
            DB::beginTransaction();

            $shipment = Shipment::where('order_id', $request->order_id)
                ->where('courier_id', $courier->id)
                ->where('status', Shipment::STATUS_ARRIVED)
                ->firstOrFail();

            // Redirect to handover options page
            return response()->json([
                'status' => 'success',
                'message' => 'Silakan pilih metode verifikasi untuk menyelesaikan pesanan.',
                'redirect' => route('kurir.delivery-photo.show', $shipment->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject delivery assignment
     */
    public function rejectDelivery(Request $request, $orderId)
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.orders')->with('error', 'Kurir tidak ditemukan');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order = Order::with(['productRental.product.shop.user', 'user'])->findOrFail($orderId);

        // Find the shipment
        $shipment = Shipment::where('order_id', $order->id)
            ->whereIn('type', [Shipment::TYPE_DELIVERY]) // REMOVE RETURN
            ->where(function ($q) use ($courier) {
                $q->where('courier_id', $courier->id)
                    ->orWhereNull('courier_id');
            })
            ->whereIn('status', [Shipment::STATUS_PENDING, Shipment::STATUS_ASSIGNED])
            ->first();

        if (!$shipment) {
            return back()->with('error', 'Pengiriman tidak ditemukan atau sudah tidak bisa ditolak');
        }

        // Reject the shipment
        if ($shipment->rejectByCourier($courier->id, $request->rejection_reason)) {
            Log::info('Courier rejected delivery', [
                'courier_id' => $courier->id,
                'order_id' => $order->id,
                'reason' => $request->rejection_reason,
            ]);

            // 🔔 SEND NOTIFICATION TO SELLER
            \App\Helpers\CourierNotificationHelper::notifySellerRejection($order, $courier, $request->rejection_reason);

            // Shipment will remain as rejected until seller manually reassigns
            // No automatic reassignment - seller must approve new courier assignment

            return redirect()->route('kurir.orders')
                ->with('success', 'Pengiriman berhasil ditolak. Pemberitahuan telah dikirim ke penjual.');
        }

        return back()->with('error', 'Gagal menolak pengiriman');
    }

    /**
     * Accept delivery assignment
     */
    public function acceptDelivery($orderId)
    {
        $courier = Courier::where('user_id', Auth::id())->first();

        if (!$courier) {
            return redirect()->route('kurir.orders')->with('error', 'Kurir tidak ditemukan');
        }

        $order = Order::with(['productRental.product.shop', 'user'])->findOrFail($orderId);

        // Find the shipment (could be assigned to me or in pool)
        $shipment = Shipment::where('order_id', $order->id)
            ->whereIn('type', [Shipment::TYPE_DELIVERY]) // REMOVE RETURN
            ->where(function ($q) use ($courier) {
                $q->where('courier_id', $courier->id)
                    ->orWhereNull('courier_id');
            })
            ->where('status', Shipment::STATUS_PENDING)
            ->first();

        if (!$shipment) {
            return back()->with('error', 'Pengiriman tidak ditemukan atau sudah tidak bisa diterima');
        }

        // Accept the shipment
        if ($shipment->acceptByCourier($courier->id)) {
            Log::info('Courier accepted delivery', [
                'courier_id' => $courier->id,
                'order_id' => $order->id,
            ]);

            return redirect()->route('kurir.orders')
                ->with('success', 'Pengiriman berhasil diterima. Silakan lanjutkan pengiriman.');
        }

        return back()->with('error', 'Gagal menerima pengiriman');
    }
}
