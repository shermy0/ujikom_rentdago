<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CourierAppNotificationHelper;

use App\Models\Shipment;
use App\Models\Courier;

class CourierNotificationController extends Controller
{
    /**
     * =========================================
     * 1. GET SEMUA NOTIFIKASI
     * =========================================
     */
    public function getAllNotifications()
    {
        // Cek apakah user sudah login
        if (!auth()->check()) {
            // Kalau belum login, return kosong + status 401 (unauthorized)
            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ], 401);
        }

        // Ambil ID user yang sedang login
        $userId = auth()->id();

        // Return semua notifikasi + jumlah yang belum dibaca
        return response()->json([
            'notifications' => CourierAppNotificationHelper::get($userId), // ambil semua notif
            'unread_count'  => CourierAppNotificationHelper::getUnreadCount($userId), // hitung unread
        ]);
    }

    /**
     * =========================================
     * 2. MARK NOTIFIKASI TERTENTU SEBAGAI DIBACA
     * =========================================
     */
    public function markAsRead(Request $request)
    {
        // Validasi input dari frontend
        $request->validate([
            'notification_ids' => 'required|array', // harus array
            'notification_ids.*' => 'required|string' // tiap item harus string
        ]);

        // Ambil ID user login
        $userId = Auth::id();

        // Tandai notif sebagai sudah dibaca
        $markedCount = CourierAppNotificationHelper::markAsRead(
            $userId,
            $request->notification_ids
        );

        // Return hasilnya
        return response()->json([
            'success' => true,
            'marked_count' => $markedCount // berapa notif yang berhasil ditandai
        ]);
    }

    /**
     * =========================================
     * 3. MARK SEMUA NOTIFIKASI SEBAGAI DIBACA
     * =========================================
     */
    public function markAllAsRead(Request $request)
    {
        // Validasi tetap pakai array (semua ID dikirim)
        $request->validate([
            'notification_ids' => 'required|array'
        ]);

        // Ambil ID user
        $userId = Auth::id();

        // Gunakan helper yang sama
        $markedCount = CourierAppNotificationHelper::markAsRead(
            $userId,
            $request->notification_ids
        );

        // Return hasil
        return response()->json([
            'success' => true,
            'marked_count' => $markedCount
        ]);
    }

    /**
     * =========================================
     * 4. HAPUS SEMUA NOTIFIKASI
     * =========================================
     */
    public function clearAll()
    {
        // Ambil ID user
        $userId = Auth::id();

        // Hapus semua notifikasi milik user
        CourierAppNotificationHelper::clear($userId);

        // Return response sukses
        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * =========================================
     * 5. HITUNG TASK PENDING (BADGE)
     * =========================================
     */
    public function getPendingTasksCount()
    {
        // Ambil user yang login
        $user = Auth::user();

        // Cari data courier berdasarkan user_id
        $courier = Courier::where('user_id', $user->id)->first();

        // Kalau user bukan courier → return 0
        if (!$courier) {
            return response()->json(['success' => true, 'count' => 0]);
        }

        /**
         * LOGIKA:
         * Hitung jumlah pengiriman (shipment) yang masih pending
         * dengan 2 kondisi:
         * 
         * 1. Pengiriman yang SUDAH ditugaskan ke kurir ini
         * 2. Pengiriman yang BELUM ada kurir (pool) dari toko yang sama
         */

        $count = Shipment::where('type', Shipment::TYPE_DELIVERY)

            // ===============================
            // KONDISI 1: SUDAH ADA KURIR
            // ===============================
            ->where(function ($q) use ($courier) {
                $q->where('courier_id', $courier->id) // kurir ini
                  ->where('status', Shipment::STATUS_PENDING); // status masih pending
            })

            // ===============================
            // KONDISI 2: BELUM ADA KURIR (POOL)
            // ===============================
            ->orWhere(function ($q) use ($courier) {
                $q->whereNull('courier_id') // belum ada kurir
                  ->where('status', Shipment::STATUS_PENDING) // masih pending

                  // pastikan dari toko yang sama
                  ->whereHas('order.productRental.product.shop', function ($sq) use ($courier) {
                      $sq->where('id', $courier->shop_id);
                  })

                  // ===============================
                  // EXCLUDE YANG SUDAH DITOLAK
                  // ===============================
                  ->where(function ($subQ) use ($courier) {
                      $subQ->whereNull('rejected_by') // belum pernah ditolak
                           ->orWhereJsonDoesntContain('rejected_by', $courier->id); 
                           // atau belum ditolak oleh kurir ini
                  });
            })

            // Hitung total data
            ->count();

        // Return jumlah task
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
