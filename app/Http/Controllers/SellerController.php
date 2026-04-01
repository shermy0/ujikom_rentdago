<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductRental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class SellerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        if ($shopId) {
            // Hitung total active rentals
            $totalActiveRentals = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->whereIn('status', ['confirmed', 'ongoing', 'arrived'])
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Hitung total revenue hari ini
            $totalRevenue = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->whereDate('paid_at', Carbon::today())
                ->sum('total_amount') ?? 0;

            // Ambil data recent orders
            $recentOrders = Order::with(['user', 'productRental.product', 'payment'])
                ->whereHas('productRental.product', function ($query) use ($shopId) {
                    $query->where('shop_id', $shopId);
                })
                ->whereIn('status', ['confirmed', 'ongoing', 'arrived', 'penalty'])
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Total produk dari shop ini
            $totalProduk = $user->shop->products()->count();

            // Total pesanan (semua status yang sudah paid)
            $totalPesanan = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Total pendapatan keseluruhan
            $totalPendapatan = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->sum('total_amount') ?? 0;
        } else {
            // Jika seller belum punya toko
            $totalActiveRentals = 0;
            $totalRevenue = 0;
            $recentOrders = collect();
            $totalProduk = 0;
            $totalPesanan = 0;
            $totalPendapatan = 0;
        }

        $rating = 0;
        $unreadNotif = 0;

        return view('seller.dashboard.index', compact(
            'user',
            'totalProduk',
            'totalPesanan',
            'totalPendapatan',
            'totalActiveRentals',
            'totalRevenue',
            'rating',
            'recentOrders',
            'unreadNotif'
        ))->with('title', 'Dashboard Seller');
    }

    public function showOrder($id)
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        if (!$shopId) {
            return redirect()->route('seller.orders')->with('error', 'Anda belum memiliki toko');
        }

        // Ambil order dengan relasi lengkap termasuk shipments
        $order = Order::with([
            'user',
            'productRental.product.images',
            'productRental.product.shop',
            'shipments.courier.user',
            'deliveryShipment.courier.user',
            'returnShipment.courier.user',
            'orderReturn',
            'payment',
        ])
            ->whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->findOrFail($id);

        // Get all active couriers for this shop
        // Exclude courier who is currently working on this order (pending, assigned, in_transit, delivered)
        // But include couriers who have rejected (so seller can re-assign them)
        $availableCouriersQuery = \App\Models\Courier::with('user')
            ->where('shop_id', $shopId)
            ->where('status', 'active');

        // Exclude courier if shipment is in active states (not rejected, not completed)
        if ($order->deliveryShipment && $order->deliveryShipment->courier_id) {
            $activeStatuses = ['pending', 'assigned', 'in_transit', 'delivered'];
            if (in_array($order->deliveryShipment->status, $activeStatuses)) {
                $availableCouriersQuery->where('id', '!=', $order->deliveryShipment->courier_id);
            }
        }

        $availableCouriers = $availableCouriersQuery->get();

        // Auto-generate QR Code jika belum ada (support lokal & hosting)
        if (
            in_array($order->status, ['confirmed', 'ongoing']) &&
            $order->payment?->payment_status === 'paid' &&
            !$order->qr_code
        ) {
            try {
                $qrCodePath = 'qrcodes/' . $order->order_code . '.png';
                $fullPath = storage_path('app/public/' . $qrCodePath);

                if (!file_exists(storage_path('app/public/qrcodes'))) {
                    mkdir(storage_path('app/public/qrcodes'), 0755, true);
                }

                QrCode::format('png')
                    ->size(400)
                    ->margin(2)
                    ->errorCorrection('H')
                    ->generate($order->order_code, $fullPath);

                $order->update(['qr_code' => $qrCodePath]);
                $order->qr_code = $qrCodePath; // update in-memory juga
            } catch (\Exception $e) {
                Log::error('Auto QR generation failed for seller order detail', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return view('seller.orders.show', compact('order', 'availableCouriers'))
            ->with('title', 'Order Detail');
    }

    public function showAssignCourier($id)
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        if (!$shopId) {
            return redirect()->route('seller.orders')->with('error', 'Anda belum memiliki toko');
        }

        // Ambil order dengan relasi lengkap
        $order = Order::with([
            'user',
            'productRental.product.images',
            'productRental.product.shop',
            'deliveryShipment'
        ])
            ->whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->findOrFail($id);

        // Validate delivery method
        if ($order->delivery_method !== 'delivery') {
            return redirect()->route('seller.orders.show', $id)
                ->with('error', 'Courier can only be assigned to delivery orders');
        }

        // Get all active couriers for this shop
        // Exclude courier who is currently working on this order (pending, assigned, in_transit, delivered)
        // But include couriers who have rejected (so seller can re-assign them)
        $availableCouriersQuery = \App\Models\Courier::with('user')
            ->where('shop_id', $shopId)
            ->where('status', 'active');

        // Exclude courier if shipment is in active states (not rejected, not completed)
        if ($order->deliveryShipment && $order->deliveryShipment->courier_id) {
            $activeStatuses = ['pending', 'assigned', 'in_transit', 'delivered'];
            if (in_array($order->deliveryShipment->status, $activeStatuses)) {
                $availableCouriersQuery->where('id', '!=', $order->deliveryShipment->courier_id);
            }
        }

        $availableCouriers = $availableCouriersQuery->get();

        return view('seller.orders.show', compact('order', 'availableCouriers'))
            ->with('title', 'Assign Courier');
    }



    public function courierAssignments()
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        if (!$shopId) {
            return redirect()->route('seller.orders')->with('error', 'Anda belum memiliki toko');
        }

        // Get orders that need courier assignment or are waiting for courier approval
        $orders = Order::with([
            'user',
            'productRental.product.images',
            'deliveryShipment.courier.user'
        ])
            ->whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->where('payment_status', 'paid')
            ->where('delivery_method', 'delivery')
            ->where(function ($query) {
                // Orders without shipment (never assigned)
                $query->whereDoesntHave('deliveryShipment')
                    // OR orders with rejected shipment (courier rejected)
                    ->orWhereHas('deliveryShipment', function ($q) {
                        $q->where('status', 'rejected');
                    })
                    // OR orders with pending shipment (waiting for courier to accept)
                    ->orWhereHas('deliveryShipment', function ($q) {
                        $q->where('status', 'pending');
                    })
                    // OR orders with assigned shipment (courier accepted but not started yet)
                    ->orWhereHas('deliveryShipment', function ($q) {
                        $q->where('status', 'assigned');
                    });
            })
            ->orderByRaw("
            CASE
                WHEN EXISTS (
                    SELECT 1 FROM shipments
                    WHERE shipments.order_id = orders.id
                    AND shipments.type = 'delivery'
                    AND shipments.status = 'rejected'
                ) THEN 1
                WHEN EXISTS (
                    SELECT 1 FROM shipments
                    WHERE shipments.order_id = orders.id
                    AND shipments.type = 'delivery'
                    AND shipments.status = 'pending'
                ) THEN 2
                ELSE 3
            END
        ")
            ->orderBy('created_at', 'desc')
            ->get();

        // Count stats
        $rejectedCount = $orders->filter(function ($order) {
            return $order->deliveryShipment && $order->deliveryShipment->status === 'rejected';
        })->count();

        // Pending = no shipment (belum assign)
        $pendingCount = $orders->filter(function ($order) {
            return !$order->deliveryShipment;
        })->count();

        // Waiting = pending shipment (menunggu kurir approve)
        $waitingCount = $orders->filter(function ($order) {
            return $order->deliveryShipment && $order->deliveryShipment->status === 'pending';
        })->count();

        // Assigned = courier accepted (assigned, in_transit, delivered)
        $assignedCount = $orders->filter(function ($order) {
            return $order->deliveryShipment && in_array($order->deliveryShipment->status, ['assigned', 'in_transit', 'delivered']);
        })->count();

        return view('seller.orders.show', compact('orders', 'rejectedCount', 'pendingCount', 'waitingCount', 'assignedCount'))
            ->with('title', 'Courier Assignment');
    }

    public function orders()
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        if ($shopId) {
            $orders = Order::with([
                'user',
                'productRental.product.images',
                'productRental.product.shop',
                'orderReturn',
                'deliveryShipment',
                'returnShipment'
            ])
                ->whereHas('productRental.product', function ($query) use ($shopId) {
                    $query->where('shop_id', $shopId);
                })
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                // Tampilkan semua status termasuk penalty dari table orders
                ->whereIn('status', ['confirmed', 'ongoing', 'completed', 'arrived', 'penalty'])
                ->orderByRaw("
                CASE
                    -- Priority 1: Status penalty dengan delivery (perlu action segera)
                    WHEN status = 'penalty' AND delivery_method = 'delivery' THEN 1
                    -- Priority 2: Status penalty dengan pickup
                    WHEN status = 'penalty' AND delivery_method = 'pickup' THEN 2
                    -- Priority 3: Order dengan unpaid penalty dari order_returns
                    WHEN EXISTS (
                        SELECT 1 FROM order_returns
                        WHERE order_returns.order_id = orders.id
                        AND order_returns.payment_status = 'unpaid'
                    ) AND delivery_method = 'delivery' THEN 3
                    -- Priority 4: Unpaid penalty pickup
                    WHEN EXISTS (
                        SELECT 1 FROM order_returns
                        WHERE order_returns.order_id = orders.id
                        AND order_returns.payment_status = 'unpaid'
                    ) AND delivery_method = 'pickup' THEN 4
                    -- Priority 5: Confirmed dengan delivery (perlu assign courier)
                    WHEN status = 'confirmed' AND delivery_method = 'delivery' THEN 5
                    -- Priority 6: Confirmed dengan pickup (tunggu customer pickup)
                    WHEN status = 'confirmed' AND delivery_method = 'pickup' THEN 6
                    -- Priority 7: Arrived (barang sudah sampai, tunggu konfirmasi)
                    WHEN status = 'arrived' THEN 7
                    -- Priority 8: Ongoing dengan delivery (sedang berjalan)
                    WHEN status = 'ongoing' AND delivery_method = 'delivery' THEN 8
                    -- Priority 9: Ongoing dengan pickup
                    WHEN status = 'ongoing' AND delivery_method = 'pickup' THEN 9
                    -- Priority 10: Completed dengan delivery (sudah selesai)
                    WHEN status = 'completed' AND delivery_method = 'delivery' THEN 10
                    -- Priority 11: Completed dengan pickup
                    WHEN status = 'completed' AND delivery_method = 'pickup' THEN 11
                    ELSE 12
                END
            ")
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $orders = Order::with(['user', 'productRental.product.images', 'orderReturn'])
                ->whereRaw('1 = 0')
                ->paginate(10);
        }

        return view('seller.orders.index', compact('orders'))
            ->with('title', 'Semua Pesanan');
    }

    public function analytics(Request $request)
    {
        $user = Auth::user();
        $shopId = optional($user->shop)->id;

        $period = $request->get('period', 'month');

        if ($shopId) {
            // Total Pendapatan Keseluruhan
            $totalRevenue = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->sum('total_amount') ?? 0;

            // Total Pesanan Completed
            $totalCompleted = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('status', 'completed')
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Total Pesanan Ongoing (termasuk arrived)
            $totalOngoing = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->whereIn('status', ['ongoing', 'arrived'])
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Total Pesanan Penalty
            $totalPenalty = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('status', 'penalty')
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Total Semua Pesanan (termasuk penalty)
            $totalOrders = Order::whereHas('productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->count();

            // Pendapatan Hari Ini
            $revenueToday = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->whereDate('paid_at', Carbon::today())
                ->sum('total_amount') ?? 0;

            // Pendapatan Minggu Ini
            $revenueWeek = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->whereBetween('paid_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('total_amount') ?? 0;

            // Pendapatan Bulan Ini
            $revenueMonth = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->whereMonth('paid_at', Carbon::now()->month)
                ->whereYear('paid_at', Carbon::now()->year)
                ->sum('total_amount') ?? 0;

            // Pendapatan Tahun Ini
            $revenueYear = Payment::whereHas('order.productRental.product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
                ->where('payment_status', 'paid')
                ->whereYear('paid_at', Carbon::now()->year)
                ->sum('total_amount') ?? 0;
        } else {
            $totalRevenue = 0;
            $totalCompleted = 0;
            $totalOngoing = 0;
            $totalPenalty = 0;
            $totalOrders = 0;
            $revenueToday = 0;
            $revenueWeek = 0;
            $revenueMonth = 0;
            $revenueYear = 0;
        }

        return view('seller.analytics.index', compact(
            'totalRevenue',
            'totalCompleted',
            'totalOngoing',
            'totalPenalty',
            'totalOrders',
            'revenueToday',
            'revenueWeek',
            'revenueMonth',
            'revenueYear',
            'period'
        ))->with('title', 'Analitik Bisnis');
    }
}
