<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CustomerNotificationHelper;

/**
 * CustomerNotificationController — Mengelola notifikasi in-app milik Customer.
 *
 * Menyediakan endpoint API untuk mengambil semua notifikasi, menandai
 * notifikasi sebagai sudah dibaca (satu per satu atau sekaligus),
 * serta menghapus seluruh notifikasi customer.
 */
class CustomerNotificationController extends Controller
{
    /**
     * Mengambil seluruh notifikasi beserta jumlah yang belum dibaca.
     *
     * Hanya tersedia untuk customer yang sudah login (auth check dilakukan di awal).
     * Digunakan oleh frontend untuk mengisi panel notifikasi secara real-time.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   public function getAllNotifications()
{
    if (!auth()->check()) {
        return response()->json([
            'notifications' => [],
            'unread_count' => 0
        ], 401);
    }

    $userId = auth()->id();

    return response()->json([
        'notifications' => CustomerNotificationHelper::get($userId),
        'unread_count'  => CustomerNotificationHelper::getUnreadCount($userId),
    ]);
}

    /**
     * Menandai notifikasi tertentu sebagai sudah dibaca.
     *
     * Menerima array ID notifikasi dari request dan menandainya
     * sebagai dibaca. Mengembalikan jumlah notifikasi yang berhasil diupdate.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|string'
        ]);

        $userId = Auth::id();
        $markedCount = CustomerNotificationHelper::markAsRead(
            $userId,
            $request->notification_ids
        );

        return response()->json([
            'success' => true,
            'marked_count' => $markedCount
        ]);
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca sekaligus.
     *
     * Menerima list ID notifikasi yang dipilih dari frontend
     * dan menandai semuanya sebagai dibaca dalam satu operasi.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array'
        ]);

        $userId = Auth::id();
        $markedCount = CustomerNotificationHelper::markAsRead(
            $userId,
            $request->notification_ids
        );

        return response()->json([
            'success' => true,
            'marked_count' => $markedCount
        ]);
    }

    /**
     * Menghapus seluruh notifikasi milik customer yang sedang login.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAll()
    {
        $userId = Auth::id();
        CustomerNotificationHelper::clear($userId);

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }
}