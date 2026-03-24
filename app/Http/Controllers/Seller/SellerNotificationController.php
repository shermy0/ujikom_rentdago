<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SellerNotificationController extends Controller
{
    /**
     * ✅ UNIFIED ENDPOINT - Get all notifications (ORDER + CHAT + COURIER)
     */
    public function getAllNotifications()
    {
        try {
            $shop = Auth::user()->shop;

            if (!$shop) {
                Log::warning('⚠️ Shop not found for user', ['user_id' => Auth::id()]);
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0
                ]);
            }

            Log::info('🔍 Fetching all notifications', ['shop_id' => $shop->id]);

            // Get all 3 types
            $orderNotifications = $this->getOrderNotifications($shop);
            $chatNotifications = $this->getChatNotifications($shop);
            $courierNotifications = $this->getCourierNotifications($shop);

            Log::info('📊 Notifications breakdown', [
                'shop_id' => $shop->id,
                'orders' => count($orderNotifications),
                'chats' => count($chatNotifications),
                'courier' => count($courierNotifications)
            ]);

            // Merge all
            $allNotifications = array_merge($orderNotifications, $chatNotifications, $courierNotifications);

            // Sort by timestamp (newest first)
            usort($allNotifications, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Remove duplicates
            $uniqueNotifications = [];
            $seenIds = [];

            foreach ($allNotifications as $notif) {
                if (!in_array($notif['id'], $seenIds)) {
                    $uniqueNotifications[] = $notif;
                    $seenIds[] = $notif['id'];
                }
            }

            // Limit to 20
            $uniqueNotifications = array_slice($uniqueNotifications, 0, 20);

            // Count unread
            $unreadCount = array_reduce($uniqueNotifications, function ($carry, $notif) {
                return $carry + (!$notif['is_read'] ? 1 : 0);
            }, 0);

            Log::info('✅ Notifications prepared', [
                'total' => count($uniqueNotifications),
                'unread' => $unreadCount
            ]);

            return response()->json([
                'notifications' => $uniqueNotifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error fetching all notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Order Notifications
     */
    private function getOrderNotifications($shop)
    {
        try {
            $orders = Order::with(['productRental.product', 'user'])
                ->whereHas('productRental.product', function ($query) use ($shop) {
                    $query->where('shop_id', $shop->id);
                })
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('status', 'cancelled')
                            ->where('updated_at', '>=', now()->subHours(24));
                    })
                        ->orWhereIn('status', ['pending', 'confirmed', 'ongoing', 'arrived']);
                })
                ->orderBy('updated_at', 'desc')
                ->limit(15)
                ->get();

            $statusLabels = [
                'pending' => 'Pesanan Menunggu Pembayaran',
                'confirmed' => 'Pesanan Dikonfirmasi',
                'ongoing' => 'Pesanan Sedang Berlangsung',
                'completed' => 'Pesanan Selesai',
                'cancelled' => 'Pesanan Dibatalkan',
                'penalty' => 'Pesanan Terkena Denda',
                'arrived' => 'Pesanan Telah Tiba',
                'on_the_way' => 'Pesanan Dalam Perjalanan'
            ];

            return $orders->map(function ($order) use ($statusLabels) {
                $productName = $order->productRental->product->name ?? 'Produk';
                $description = "{$productName} - #{$order->order_code}";

                return [
                    'id' => 'order-' . $order->id,
                    'type' => 'order',
                    'subtype' => $order->status,
                    'title' => $statusLabels[$order->status] ?? 'Pesanan Baru',
                    'description' => $description,
                    'time' => $this->formatTimeAgo($order->updated_at),
                    'timestamp' => $order->updated_at->toIso8601String(),
                    'is_read' => (bool) $order->is_read_by_seller,
                    'url' => '/seller/orders/' . $order->id
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('❌ Error getting order notifications', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get Chat Notifications
     */
    private function getChatNotifications($shop)
    {
        try {
            $conversations = Conversation::where('shop_id', $shop->id)
                ->with(['customer', 'lastMessage.sender'])
                ->has('messages')
                ->orderBy('last_message_at', 'desc')
                ->limit(10)
                ->get();

            return $conversations->map(function ($conv) {
                $unreadCount = ChatMessage::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $customerName = $conv->customer->name ?? 'Customer';
                $lastMsg = $conv->lastMessage->message ?? 'Pesan baru';

                if (strlen($lastMsg) > 50) {
                    $lastMsg = substr($lastMsg, 0, 50) . '...';
                }

                return [
                    'id' => 'chat-' . $conv->id,
                    'type' => 'chat',
                    'subtype' => null,
                    'title' => "Pesan dari {$customerName}",
                    'description' => $lastMsg,
                    'time' => $this->formatTimeAgo($conv->last_message_at),
                    'timestamp' => $conv->last_message_at
                        ? Carbon::parse($conv->last_message_at)->toIso8601String()
                        : now()->toIso8601String(),
                    'is_read' => $unreadCount === 0,
                    'url' => '/seller/chat/' . $conv->customer_id
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('❌ Error getting chat notifications', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * ✅ Get Courier Notifications (FIXED)
     */
    private function getCourierNotifications($shop)
    {
        try {
            $notifications = \App\Helpers\CourierNotificationHelper::get($shop->id);

            Log::info('📦 Courier notifications fetched', [
                'shop_id' => $shop->id,
                'count' => count($notifications)
            ]);

            return $notifications;
        } catch (\Exception $e) {
            Log::error('❌ Error fetching courier notifications', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Format time ago
     */
    private function formatTimeAgo($dateTime)
    {
        if (!$dateTime) {
            return 'Baru saja';
        }

        $date = $dateTime instanceof Carbon ? $dateTime : Carbon::parse($dateTime);
        $now = Carbon::now();

        $diffInMinutes = $now->diffInMinutes($date);
        $diffInHours = $now->diffInHours($date);
        $diffInDays = $now->diffInDays($date);

        if ($diffInMinutes < 1) return 'Baru saja';
        if ($diffInMinutes < 60) return "{$diffInMinutes} menit yang lalu";
        if ($diffInHours < 24) return "{$diffInHours} jam yang lalu";
        if ($diffInDays < 7) return "{$diffInDays} hari yang lalu";

        return $date->format('d M');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $shop = Auth::user()->shop;

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found'
                ], 404);
            }

            $notificationIds = $request->input('notification_ids', []);

            if (empty($notificationIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No notifications to mark',
                    'marked_count' => 0
                ]);
            }

            $orderIds = [];
            $conversationIds = [];
            $courierIds = [];

            foreach ($notificationIds as $id) {
                if (strpos($id, 'order-') === 0) {
                    $orderIds[] = str_replace('order-', '', $id);
                } elseif (strpos($id, 'chat-') === 0) {
                    $conversationIds[] = str_replace('chat-', '', $id);
                } elseif (strpos($id, 'courier-') === 0) {
                    $courierIds[] = $id;
                }
            }

            $markedCount = 0;

            // Mark orders
            if (!empty($orderIds)) {
                $markedOrders = Order::whereIn('id', $orderIds)
                    ->whereHas('productRental.product', function ($query) use ($shop) {
                        $query->where('shop_id', $shop->id);
                    })
                    ->update(['is_read_by_seller' => true]);

                $markedCount += $markedOrders;
            }

            // Mark chats
            if (!empty($conversationIds)) {
                $markedMessages = ChatMessage::whereIn('conversation_id', $conversationIds)
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                $markedCount += $markedMessages;
            }

            // Mark courier notifications
            if (!empty($courierIds)) {
                $markedCount += \App\Helpers\CourierNotificationHelper::markAsRead($shop->id, $courierIds);
            }

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'marked_count' => $markedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request)
    {
        try {
            $type = $request->input('type');
            $notificationId = $request->input('notification_id');

            if ($type === 'chat') {
                ChatMessage::where('conversation_id', $notificationId)
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            } elseif ($type === 'order') {
                $shop = Auth::user()->shop;
                Order::where('id', $notificationId)
                    ->whereHas('productRental.product', function ($query) use ($shop) {
                        $query->where('shop_id', $shop->id);
                    })
                    ->update(['is_read_by_seller' => true]);
            } elseif ($type === 'courier') {
                $shop = Auth::user()->shop;
                \App\Helpers\CourierNotificationHelper::markAsRead($shop->id, ['courier-' . $notificationId]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get count of confirmed orders for seller (FOR ORDER BADGE)
     * Badge shows ALL confirmed orders, regardless of read status
     * Badge will disappear when status changes to 'ongoing'
     */
    public function getUnreadOrderCount()
    {
        try {
            $shop = Auth::user()->shop;

            if (!$shop) {
                return response()->json([
                    'success' => true,
                    'count' => 0
                ]);
            }

            // ✅ Count ALL confirmed orders (ignore is_read_by_seller)
            // Badge will only disappear when status changes to 'ongoing'
            $confirmedCount = Order::whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->where('status', Order::STATUS_CONFIRMED) // ✅ ONLY show badge for confirmed orders
                ->count();

            return response()->json([
                'success' => true,
                'count' => $confirmedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting confirmed order count', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => true,
                'count' => 0
            ]);
        }
    }

    /**
     * Mark all orders as read
     */
    public function markOrdersAsRead()
    {
        try {
            $shop = Auth::user()->shop;

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found'
                ]);
            }

            $updatedCount = Order::whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->where('is_read_by_seller', false)
                ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
                ->update(['is_read_by_seller' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Orders marked as read',
                'count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking orders as read', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error marking orders as read'
            ], 500);
        }
    }

    /**
     * Mark specific order as read
     */
    public function markSingleOrderAsRead($orderId)
    {
        try {
            $shop = Auth::user()->shop;

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found'
                ]);
            }

            $order = Order::whereHas('productRental.product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->findOrFail($orderId);

            $order->update(['is_read_by_seller' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking order as read', [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error marking order as read'
            ], 500);
        }
    }
}
