<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CourierAppNotificationHelper
{
    /**
     * Add courier app notification (User based)
     */
    public static function add($userId, array $data)
    {
        try {
            $cacheKey = "courier_app_notifications_user_{$userId}";
            $counterKey = "courier_app_notifications_unread_{$userId}";

            $notifications = Cache::get($cacheKey, []);

            $notification = [
                'id' => 'courier-app-' . uniqid(),
                'type' => 'assignment',
                'subtype' => $data['subtype'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'url' => $data['url'] ?? null,
                'timestamp' => now()->toIso8601String(),
                'is_read' => false,
            ];

            array_unshift($notifications, $notification);
            $notifications = array_slice($notifications, 0, 20);

            Cache::put($cacheKey, $notifications, now()->addDays(7));

            // Increment unread counter
            Cache::increment($counterKey);

            Log::info('🔔 Courier App notification added', [
                'user_id' => $userId,
                'unread' => Cache::get($counterKey),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Failed to add courier app notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 📢 Notify Courier: New Assignment
     */
    public static function notifyNewAssignment($order, $courierUserId)
    {
        try {
            $title = "Tugas Pengiriman Baru";
            $description = "Anda mendapatkan tugas pengiriman baru untuk Order #{$order->order_code}. Silakan cek dan konfirmasi.";
            
            // Adjust URL to point to courier app specific page if needed
            // For now assuming a route like courier.orders.show exists or general requests page
            $url = "/courier/requests"; 

            self::add($courierUserId, [
                'type' => 'assignment',
                'subtype' => 'new_assignment',
                'title' => $title,
                'description' => $description,
                'url' => $url
            ]);

            // Optional: Also trigger a push notification if using Firebase/OneSignal
            
        } catch (\Exception $e) {
            Log::error('Failed to notify courier about new assignment', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all notifications
     */
    public static function get($userId)
    {
        try {
            $cacheKey = "courier_app_notifications_user_{$userId}";
            $notifications = Cache::get($cacheKey, []);

            return array_map(function ($notif) {
                if (isset($notif['timestamp'])) {
                    $notif['time'] = self::formatTimeAgo($notif['timestamp']);
                }
                return $notif;
            }, $notifications);
        } catch (\Exception $e) {
            Log::error('❌ Failed to get courier app notifications', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Mark as read
     */
    public static function markAsRead($userId, array $notificationIds)
    {
        try {
            $cacheKey = "courier_app_notifications_user_{$userId}";
            $notifications = Cache::get($cacheKey, []);

            $markedCount = 0;

            foreach ($notifications as &$notif) {
                if (in_array($notif['id'], $notificationIds) && !$notif['is_read']) {
                    $notif['is_read'] = true;
                    $markedCount++;
                }
            }

            Cache::put($cacheKey, $notifications, now()->addDays(7));

            // Adjust counter
            $counterKey = "courier_app_notifications_unread_{$userId}";
            $currentCount = Cache::get($counterKey, 0);
            $newCount = max(0, $currentCount - $markedCount);
            Cache::put($counterKey, $newCount, now()->addDays(7));

            return $markedCount;
        } catch (\Exception $e) {
            Log::error('❌ Failed to mark as read', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get unread count
     */
    public static function getUnreadCount($userId)
    {
        // We can trust the detailed get() count or the cache counter. 
        // Using get() filtering is safer for consistency.
        $notifications = self::get($userId);
        return count(array_filter($notifications, fn($n) => !$n['is_read']));
    }

    /**
     * Clear all
     */
    public static function clear($userId)
    {
        Cache::forget("courier_app_notifications_user_{$userId}");
        Cache::forget("courier_app_notifications_unread_{$userId}");
        return true;
    }

    /**
     * Format time ago
     */
    private static function formatTimeAgo($timestamp)
    {
        try {
            $date = Carbon::parse($timestamp);
            $now = Carbon::now();

            $diffInMinutes = $now->diffInMinutes($date);
            $diffInHours = $now->diffInHours($date);
            $diffInDays = $now->diffInDays($date);

            if ($diffInMinutes < 1) return 'Baru saja';
            if ($diffInMinutes < 60) return "{$diffInMinutes} menit yang lalu";
            if ($diffInHours < 24) return "{$diffInHours} jam yang lalu";
            if ($diffInDays < 7) return "{$diffInDays} hari yang lalu";

            return $date->format('d M');
        } catch (\Exception $e) {
            return 'Baru saja';
        }
    }

    /**
     * Helper to send WA with robust phone formatting
     */
    private static function sendWa($targetUserId, $message)
    {
        try {
            $user = \App\Models\User::find($targetUserId);
            if (!$user || empty($user->phone)) {
                return;
            }

            $phone = $user->phone;
            // Robust formatting: remove non-digits, ensure 62 prefix
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            if (function_exists('kirimWa')) {
                kirimWa($phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('WA Send Failed in CourierApp', [
                'user_id' => $targetUserId,
                'error' => $e->getMessage()
            ]);
        }
    }

}
