<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CustomerNotificationHelper;

class CustomerNotificationController extends Controller
{
    /**
     * Get all notifications (unified endpoint)
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
     * Mark specific notifications as read
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
     * Mark all notifications as read
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
     * Clear all notifications
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