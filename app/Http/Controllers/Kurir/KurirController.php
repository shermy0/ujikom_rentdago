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
/**
 * Display courier dashboard
 */
public function index()
{
    // Ambil data kurir berdasarkan user login
    $courier = Courier::where('user_id', Auth::id())->first();

    // Default statistik (kalau kurir tidak ditemukan)
    $stats = [
        'perlu_diambil' => 0,      // paket yang belum diambil
        'sedang_dikirim' => 0,     // paket yang sedang dikirim
    ];

    if ($courier) {

        // ===============================
        // 📦 HITUNG PAKET PERLU DIAMBIL
        // ===============================
        // Status:
        // - pending (belum diterima)
        // - assigned (sudah ditugaskan)
        $stats['perlu_diambil'] = Shipment::where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->whereIn('status', [
                Shipment::STATUS_PENDING,
                Shipment::STATUS_ASSIGNED
            ])
            ->count();

        // ===============================
        // 🚚 HITUNG YANG SEDANG DIKIRIM
        // ===============================
        // Status:
        // - picked_up (sudah diambil)
        // - on_the_way (sedang jalan)
        $stats['sedang_dikirim'] = Shipment::where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->whereIn('status', [
                Shipment::STATUS_PICKED_UP,
                Shipment::STATUS_ON_THE_WAY
            ])
            ->count();

        // ===============================
        // 📊 AMBIL AKTIVITAS TERAKHIR
        // ===============================
        $recentShipment = Shipment::with('order.user')
            ->where('courier_id', $courier->id)
            ->where('type', Shipment::TYPE_DELIVERY)
            ->orderBy('updated_at', 'desc') // terbaru
            ->first();

    } else {
        $recentShipment = null;
    }

    // Kirim ke view
    return view('kurir.index', compact('stats', 'recentShipment'))
        ->with('title', 'Beranda');
}
/**
 * Display courier orders list
 */
public function orders()
{
    // Ambil kurir login
    $courier = Courier::where('user_id', Auth::id())->first();

    // Kalau tidak ada kurir → kosongkan
    if (!$courier) {
        return view('kurir.orders', ['orders' => collect([])]);
    }

    // ===============================
    // 📦 AMBIL ORDER BERDASARKAN SHIPMENT
    // ===============================
    $orders = Order::with([
        'user',
        'productRental.product.images',
        'productRental.product.shop',
        'shipments' => function ($query) {
            $query->where('type', Shipment::TYPE_DELIVERY);
        }
    ])
    ->whereHas('shipments', function ($query) use ($courier) {

        $query->whereIn('type', [Shipment::TYPE_DELIVERY])

            // ===============================
            // PRIORITAS 1: YANG DITUGASKAN KE SAYA
            // ===============================
            ->where(function ($q) use ($courier) {
                $q->where('courier_id', $courier->id)
                  ->whereIn('status', [
                      Shipment::STATUS_PENDING,
                      Shipment::STATUS_ASSIGNED,
                      Shipment::STATUS_PICKED_UP,
                      Shipment::STATUS_ON_THE_WAY,
                      Shipment::STATUS_ARRIVED
                  ]);
            })

            // ===============================
            // PRIORITAS 2: POOL (BELUM ADA KURIR)
            // ===============================
            ->orWhere(function ($q) use ($courier) {
                $q->whereNull('courier_id') // belum diambil
                  ->where('status', Shipment::STATUS_PENDING)
                  ->whereHas('order.productRental.product.shop', function ($sq) use ($courier) {
                      $sq->where('id', $courier->shop_id); // harus toko yang sama
                  });
            });
    })
    ->get()

    // ===============================
    // 🔍 FILTER LAGI DI COLLECTION
    // ===============================
    ->filter(function ($order) use ($courier) {

        // Ambil shipment terbaru
        $shipment = $order->shipments
            ->where('type', Shipment::TYPE_DELIVERY)
            ->sortByDesc('created_at')
            ->first();

        if (!$shipment) return false;

        // Kalau memang ditugaskan ke saya → tampilkan
        if ($shipment->courier_id === $courier->id) {
            return true;
        }

        // Kalau bukan milik saya → cek apakah pernah ditolak
        return !$shipment->hasBeenRejectedBy($courier->id);
    })
    ->values();

    return view('kurir.orders', compact('orders'))
        ->with('title', 'Pesanan');
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

    // Kalau tidak ada kurir
    if (!$courier) {
        return view('kurir.history', [
            'shipments' => collect([]),
            'todayCount' => 0,
            'weekCount' => 0,
            'monthCount' => 0
        ]);
    }

    // ===============================
    // 📦 AMBIL SHIPMENT SELESAI
    // ===============================
    $shipments = Shipment::with([
        'order.user',
        'order.productRental.product.images',
        'order.productRental.product.shop',
        'order.address'
    ])
    ->whereIn('type', [Shipment::TYPE_DELIVERY])

    // Milik kurir ATAU pernah ditolak oleh kurir
    ->where(function ($q) use ($courier) {
        $q->where('courier_id', $courier->id)
          ->orWhereJsonContains('rejected_by', $courier->id);
    })

    // Status selesai
    ->whereIn('status', [
        Shipment::STATUS_DELIVERED,
        Shipment::STATUS_FAILED,
        Shipment::STATUS_RETURNED,
        Shipment::STATUS_REJECTED
    ])
    ->orderBy('updated_at', 'desc')
    ->get();

    $now = now();

    // ===============================
    // 📊 STATISTIK (HANYA DELIVERED)
    // ===============================
    $todayCount = $shipments->filter(fn($s) =>
        $s->updated_at->isToday() &&
        $s->status === Shipment::STATUS_DELIVERED
    )->count();

    $weekCount = $shipments->filter(fn($s) =>
        $s->updated_at->isCurrentWeek() &&
        $s->status === Shipment::STATUS_DELIVERED
    )->count();

    $monthCount = $shipments->filter(fn($s) =>
        $s->updated_at->isCurrentMonth() &&
        $s->status === Shipment::STATUS_DELIVERED
    )->count();

    return view('kurir.history', compact(
        'shipments',
        'todayCount',
        'weekCount',
        'monthCount'
    ))->with('title', 'Riwayat');
}
    /**
     * Display courier profile
     */
public function profile()
{
    $courier = Courier::where('user_id', Auth::id())->first();

    $totalCount = 0;
    $monthCount = 0;

    if ($courier) {

        // Ambil semua shipment yang berhasil
        $delivered = Shipment::where('courier_id', $courier->id)
            ->where('status', Shipment::STATUS_DELIVERED)
            ->get();

        // Total semua
        $totalCount = $delivered->count();

        // Total bulan ini
        $monthCount = $delivered->filter(fn($s) =>
            $s->updated_at->isCurrentMonth()
        )->count();
    }

    return view('kurir.profile', compact('totalCount', 'monthCount'))
        ->with('title', 'Profil');
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
    // 🔍 Ambil data kurir berdasarkan user yang sedang login
    $courier = Courier::where('user_id', Auth::id())->first();

    // ❌ Jika user tidak punya data kurir → tidak boleh lanjut
    if (!$courier) {
        return redirect()->route('kurir.orders')->with('error', 'Kurir tidak ditemukan');
    }

    // ✅ Validasi input dari request (harus ada alasan penolakan)
    $request->validate([
        'rejection_reason' => 'required|string|max:500',
    ]);

    // 🔍 Ambil data order beserta relasi:
    // - user (customer)
    // - product → shop → user (seller)
    $order = Order::with(['productRental.product.shop.user', 'user'])->findOrFail($orderId);

    // 🔍 Cari shipment yang sesuai dengan kondisi:
    $shipment = Shipment::where('order_id', $order->id)

        // Hanya untuk tipe DELIVERY (bukan RETURN)
        ->whereIn('type', [Shipment::TYPE_DELIVERY])

        // Kondisi:
        ->where(function ($q) use ($courier) {
            $q->where('courier_id', $courier->id) // 1. shipment milik kurir ini
              ->orWhereNull('courier_id');       // 2. atau masih di pool (belum ada kurir)
        })

        // Status yang masih boleh ditolak
        ->whereIn('status', [
            Shipment::STATUS_PENDING,   // belum diterima
            Shipment::STATUS_ASSIGNED   // sudah ditugaskan
        ])

        // Ambil satu data pertama yang cocok
        ->first();

    // ❌ Jika shipment tidak ditemukan atau sudah tidak valid
    if (!$shipment) {
        return back()->with('error', 'Pengiriman tidak ditemukan atau sudah tidak bisa ditolak');
    }

    // 🚫 Proses penolakan oleh kurir
    // Method ini biasanya:
    // - menambahkan ID kurir ke field rejected_by (JSON)
    // - menyimpan alasan penolakan
    if ($shipment->rejectByCourier($courier->id, $request->rejection_reason)) {

        // 📝 Catat log untuk debugging / histori sistem
        Log::info('Courier rejected delivery', [
            'courier_id' => $courier->id,
            'order_id' => $order->id,
            'reason' => $request->rejection_reason,
        ]);

        // 🔔 Kirim notifikasi ke seller bahwa kurir menolak
        \App\Helpers\CourierNotificationHelper::notifySellerRejection(
            $order,
            $courier,
            $request->rejection_reason
        );

        // ℹ️ Catatan:
        // - Shipment tetap ada (tidak dihapus)
        // - Status tetap "rejected"
        // - Seller harus assign ulang secara manual

        // ✅ Redirect kembali ke halaman orders dengan pesan sukses
        return redirect()->route('kurir.orders')
            ->with('success', 'Pengiriman berhasil ditolak. Pemberitahuan telah dikirim ke penjual.');
    }

    // ❌ Jika proses reject gagal
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

       // Cari shipment pending
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
