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
     * Get all notifications
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
            'notifications' => CourierAppNotificationHelper::get($userId),
            'unread_count'  => CourierAppNotificationHelper::getUnreadCount($userId),
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
        $markedCount = CourierAppNotificationHelper::markAsRead(
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
        $markedCount = CourierAppNotificationHelper::markAsRead(
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
        CourierAppNotificationHelper::clear($userId);

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Get pending tasks count for badge
     */
    public function getPendingTasksCount()
    {
        $user = Auth::user();
        $courier = Courier::where('user_id', $user->id)->first();

        if (!$courier) {
            return response()->json(['success' => true, 'count' => 0]);
        }

        // Count pending shipments (same logic as KurirController@orders)
        // 1. Assigned to this courier
        // 2. OR Pool (unassigned) from same shop
        $count = Shipment::where('type', Shipment::TYPE_DELIVERY)
            ->where(function ($q) use ($courier) {
                // Priority 1: Specifically assigned to this courier (PENDING status)
                $q->where('courier_id', $courier->id)
                    ->where('status', Shipment::STATUS_PENDING);
            })
            ->orWhere(function ($q) use ($courier) {
                // Priority 2: Pool (unassigned) from the same shop
                $q->whereNull('courier_id')
                    ->where('status', Shipment::STATUS_PENDING)
                    ->whereHas('order.productRental.product.shop', function ($sq) use ($courier) {
                        $sq->where('id', $courier->shop_id);
                    })
                    // Exclude if rejected by this courier
                    ->where(function ($subQ) use ($courier) {
                        $subQ->whereNull('rejected_by')
                            ->orWhereJsonDoesntContain('rejected_by', $courier->id);
                    });
            })
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
