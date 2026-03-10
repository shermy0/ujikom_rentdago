<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Courier;
use App\Models\Shipment;

class CourierNotificationHelper
{
    /**
     * Helper to send WA with robust phone formatting
     */
    private static function sendWa($targetUser, $message, $imageUrl = null)
    {
        try {
            if (!$targetUser || empty($targetUser->phone)) {
                return;
            }

            $phone = $targetUser->phone;
            // Robust formatting: remove non-digits, ensure 62 prefix
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            if (function_exists('kirimWa')) {
                kirimWa($phone, $message, $imageUrl);
            }
        } catch (\Exception $e) {
            Log::error('WA Send Failed', [
                'user_id' => $targetUser->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add courier notification
     */
    public static function add($shopId, array $data)
    {
        try {
            $cacheKey = "courier_notifications_shop_{$shopId}";
            $counterKey = "courier_notifications_unread_{$shopId}";

            $notifications = Cache::get($cacheKey, []);

            $notification = [
                'id' => 'courier-' . uniqid(),
                'type' => 'courier',
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

            // 🔥 INI KUNCI BELL NYALA
            Cache::increment($counterKey);

            Log::info('🔔 Courier notification added', [
                'shop_id' => $shopId,
                'unread' => Cache::get($counterKey),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Failed to add courier notification', [
                'shop_id' => $shopId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }


    /**
     * Get all notifications
     */
    public static function get($shopId)
    {
        try {
            $cacheKey = "courier_notifications_shop_{$shopId}";
            $notifications = Cache::get($cacheKey, []);

            return array_map(function ($notif) {
                if (isset($notif['timestamp'])) {
                    $notif['time'] = self::formatTimeAgo($notif['timestamp']);
                }
                return $notif;
            }, $notifications);
        } catch (\Exception $e) {
            Log::error('❌ Failed to get courier notifications', [
                'shop_id' => $shopId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Mark as read
     */
    public static function markAsRead($shopId, array $notificationIds)
    {
        try {
            $cacheKey = "courier_notifications_shop_{$shopId}";
            $notifications = Cache::get($cacheKey, []);

            $markedCount = 0;

            foreach ($notifications as &$notif) {
                if (in_array($notif['id'], $notificationIds) && !$notif['is_read']) {
                    $notif['is_read'] = true;
                    $markedCount++;
                }
            }

            Cache::put($cacheKey, $notifications, now()->addDays(7));

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
    public static function getUnreadCount($shopId)
    {
        $notifications = self::get($shopId);
        return count(array_filter($notifications, fn($n) => !$n['is_read']));
    }

    /**
     * Clear all
     */
    public static function clear($shopId)
    {
        Cache::forget("courier_notifications_shop_{$shopId}");
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
     * 📢 Notify SELLER when courier rejects delivery
     */
    public static function notifySellerRejection($order, $courier, $reason)
    {
        try {
            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            // 1. Add Local Notification
            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'rejected',
                'title' => 'Kurir Menolak Pesanan',
                'description' => "Kurir {$courier->user->name} menolak pesanan #{$order->order_code}",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // 2. Send WhatsApp Notification
            $message = "*⚠️ KURIR MENOLAK PESANAN*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Kurir *{$courier->user->name}* telah menolak pengiriman pesanan.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "❌ *Alasan Penolakan:*\n";
            $message .= $reason . "\n\n";
            $message .= "⚡ *Tindakan yang Diperlukan:*\n";
            $message .= "Silakan tugaskan kurir lain untuk pesanan ini.\n\n";
            $message .= "🔗 Lihat detail pesanan:\n";
            $message .= route('seller.orders.show', $order->id) . "\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('Rejection notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify CUSTOMER & SELLER when courier accepts delivery
     */
    public static function notifyAcceptance($order, $courier)
    {
        try {
            $shop = $order->productRental->product->shop;
            $customer = $order->user;
            $seller = $shop->user;

            // 1. Add Local Notification for Seller
            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'on_the_way', // Sync with JS: accepted is expected
                'title' => 'Kurir Menerima Pesanan',
                'description' => "Kurir {$courier->user->name} telah menerima pesanan #{$order->order_code}",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // 1.5 Add Local Notification for Customer (Bell Icon)
            \App\Helpers\CustomerNotificationHelper::notifyOrderInTransit($order, $courier);

            // 2. Send WhatsApp to Customer
            $message = "*🚚 PESANAN ANDA DALAM PERJALANAN*\n\n";
            $message .= "Halo *{$customer->name}*,\n\n";
            $message .= "Pesanan Kamu sedang dalam perjalanan!\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "🏪 Toko: *{$shop->name_store}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "⏱️ Durasi: *{$order->productRental->cycle_value} Jam*\n";
            $message .= "💰 Total: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🚗 *INFORMASI KURIR*\n";
            $message .= "👤 Nama: *{$courier->user->name}*\n";
            $message .= "📞 Telepon: {$courier->user->phone}\n\n";

            $message .= "🔗 Lacak pesanan Anda:\n";
            $message .= route('customer.order.show', $order->id) . "\n\n";
            $message .= "Mohon bersiap untuk menerima pesanan Anda! 📦✨\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($customer, $message);

        } catch (\Exception $e) {
            Log::error('Acceptance notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify Seller: Pesanan sudah sampai dan diterima
     */
    public static function notifySellerDeliveryComplete($order)
    {
        try {
            // Eager load if not loaded
            if (!$order->relationLoaded('productRental.product.shop.user')) {
                $order->load(['productRental.product.shop.user', 'user']);
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            // 1. Add Local Notification to Bell
            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'delivered',
                'title' => 'Pesanan Telah Diterima Customer',
                'description' => "Pesanan #{$order->order_code} telah diterima oleh {$order->user->name}",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // 2. Send WhatsApp
            $message = "*✅ PESANAN SUDAH SAMPAI DAN DITERIMA*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Pesanan telah berhasil diterima oleh customer!\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "🏪 Toko: *{$shop->name_store}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "💰 Total: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📅 *Waktu Penerimaan:* " . now()->format('d/m/Y H:i') . "\n";
            $message .= "⏱️ *Durasi Sewa:* {$order->productRental->cycle_value} Jam\n";
            $message .= "\n🎉 Pesanan kini sedang berlangsung!\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('Delivery complete notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify Seller: Return picked up from customer
     */
    public static function notifySellerReturnPickedUp($order)
    {
        try {
            // Eager load if not loaded
            if (!$order->relationLoaded('productRental.product.shop.user')) {
                $order->load(['productRental.product.shop.user', 'user']);
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            // 1. Add Local Notification
            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'return_pickup',
                'title' => 'Barang Dalam Perjalanan Kembali',
                'description' => "Pesanan #{$order->order_code} telah dijemput dari customer",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // 2. Send WhatsApp
            $message = "*🔄 BARANG SEDANG DALAM PERJALANAN KEMBALI*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Barang pesanan telah dijemput dari customer dan sedang dalam perjalanan kembali ke toko Anda.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📅 *Waktu Penjemputan:* " . now()->format('d/m/Y H:i') . "\n\n";
            $message .= "Mohon bersiap untuk menerima barang kembali. 📦\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('Return pickup notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify Seller: Courier is on the way
     */
    public static function notifySellerInTransit($order, $courier)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load('productRental.product.shop');
            }

            $shop = $order->productRental->product->shop;

            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'in_transit',
                'title' => 'Pesanan Dalam Perjalanan',
                'description' => "Kurir {$courier->user->name} sedang mengantar pesanan #{$order->order_code}",
                'url' => route('seller.orders.show', $order->id)
            ]);
            
            // 2. Send WhatsApp to Seller
            $seller = $shop->user;
            $message = "*🚚 PESANAN DALAM PERJALANAN*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Pesanan Anda sedang dalam perjalanan oleh kurir!\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📝 *Informasi Kurir:*\n";
            $message .= "👤 Nama: *{$courier->user->name}*\n";
            $message .= "📞 Telepon: *{$courier->user->phone}*\n\n";
            $message .= "🔗 Lihat detail:\n";
            $message .= route('seller.orders.show', $order->id) . "\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('In-transit notification failed', ['error' => $e->getMessage()]);
        }
    }
    /**
     * 📢 Notify Seller: Customer sudah dekat
     */
    public static function notifyCustomerNear($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load('productRental.product.shop');
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            self::add($shop->id, [
                'type' => 'order',
                'subtype' => 'customer_near',
                'title' => 'Customer Sudah Dekat! 📍',
                'description' => "Customer {$order->user->name} sebentar lagi sampai ke lokasi toko.",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // Send WhatsApp
            $message = "*📍 CUSTOMER SUDAH DEKAT*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Customer *{$order->user->name}* sudah hampir sampai di lokasi toko untuk pengambilan barang.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "Mohon bersiap untuk menyambut customer. 👋\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('Customer near notification failed', ['error' => $e->getMessage()]);
        }
    }
    /**
     * 📢 Notify Seller: Customer Returns Request Pickup
     */
    public static function notifySellerReturnRequest($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load('productRental.product.shop');
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;

            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'return_request',
                'title' => 'Permintaan Penjemputan Barang',
                'description' => "Customer meminta penjemputan untuk pengembalian barang. Order: #{$order->order_code}",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // WA Notification
            $message = "🔄 *PERMINTAAN PENJEMPUTAN BARANG*\n\n";
            $message .= "Halo *{$shop->user->name}*,\n\n";
            $message .= "Customer meminta penjemputan untuk pengembalian barang.\n\n";
            $message .= "📋 Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n\n";
            $message .= "Silakan buka aplikasi untuk menugaskan kurir penjemputan.\n\n";

            self::sendWa($seller, $message);

        } catch (\Exception $e) {
            Log::error('Seller return request notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify Courier: Assignment completed
     */
    public static function notifyCourierAssignment($order, $courier, $shipment)
    {
        try {
            $phone = $courier->user->phone;

            // Format phone number (remove leading 0, add 62)
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $message = "*🚚 PENUGASAN PENGIRIMAN BARU*\n\n";
            $message .= "Halo *{$courier->user->name}*,\n\n";
            $message .= "Anda telah ditugaskan untuk pengiriman:\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "⏱️ Durasi: *{$order->productRental->cycle_value} Jam*\n";
            $message .= "💰 Total: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

            $message .= "*INFORMASI CUSTOMER*\n";
            $message .= "👤 Nama: *{$order->user->name}*\n";
            $message .= "📞 Telepon: *{$order->user->phone}*\n\n";

            if ($shipment->delivery_address_snapshot) {
                $message .= "📍 *Alamat Pengiriman:*\n";
                $message .= $shipment->delivery_address_snapshot . "\n\n";
            }

            if ($shipment->courier_notes) {
                $message .= "📝 *Catatan Khusus:*\n";
                $message .= $shipment->courier_notes . "\n\n";
            }

            $message .= "📅 *Jadwal Pengiriman:*\n";
            $message .= "Mulai: " . \Carbon\Carbon::parse($order->start_time)->format('d/m/Y H:i') . "\n";
            if ($order->end_time) {
                $message .= "Selesai: " . \Carbon\Carbon::parse($order->end_time)->format('d/m/Y H:i') . "\n";
            }
            $message .= "\n";

            $message .= "Silakan cek aplikasi untuk detail lengkap.\n\n";
            
            // 1. Send In-App Notification (Bell Icon) for Courier
            \App\Helpers\CourierAppNotificationHelper::notifyNewAssignment($order, $courier->user_id);

            // 2. Send WhatsApp
            if (function_exists('kirimwa')) {
                kirimwa($phone, $message);
            } else if (function_exists('kirimWa')) {
                kirimWa($phone, $message);
            }

            Log::info('Courier assignment notification sent via Helper', [
                'order_id' => $order->id,
                'courier_id' => $courier->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Courier assignment notification failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📢 Notify Seller: Handover completed with photo
     */
    public static function notifySellerHandover($order, $shipment)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop.user')) {
                $order->load(['productRental.product.shop.user', 'user']);
            }

            $shop = $order->productRental->product->shop;
            $seller = $shop->user;
            $photoPath = $shipment->delivery_proof_photo;
            $imageUrl = $photoPath ? asset('storage/' . $photoPath) : null;

            // 1. Add Local Notification (Bell Icon)
            self::add($shop->id, [
                'type' => 'courier',
                'subtype' => 'handover_photo',
                'title' => 'Bukti Foto Penyerahan Unit',
                'description' => "Kurir telah mengunggah foto bukti penyerahan untuk pesanan #{$order->order_code}",
                'url' => route('seller.orders.show', $order->id)
            ]);

            // 2. Send WhatsApp with Photo
            $message = "*📸 BUKTI PENYERAHAN UNIT*\n\n";
            $message .= "Halo *{$seller->name}*,\n\n";
            $message .= "Kurir telah menyerahkan unit dan mengunggah foto bukti penyerahan.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "👤 Customer: *{$order->user->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "📅 *Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";
            $message .= "🔗 Lihat detail & foto lengkap:\n";
            $message .= route('seller.orders.show', $order->id) . "\n\n";
            $message .= "⏰ " . now()->format('d/m/Y H:i') . "\n";

            self::sendWa($seller, $message, $imageUrl);

        } catch (\Exception $e) {
            Log::error('Handover photo notification failed', ['error' => $e->getMessage()]);
        }
    }
}
