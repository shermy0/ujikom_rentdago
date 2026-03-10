<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerNotificationHelper
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
     * Add customer notification
     */
    public static function add($userId, array $data)
    {
        try {
            $cacheKey = "customer_notifications_user_{$userId}";
            $counterKey = "customer_notifications_unread_{$userId}";

            $notifications = Cache::get($cacheKey, []);

            $notification = [
                'id' => 'customer-' . uniqid(),
                'type' => $data['type'] ?? 'order',
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

            Log::info('🔔 Customer notification added', [
                'user_id' => $userId,
                'unread' => Cache::get($counterKey),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Failed to add customer notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all notifications
     */
    public static function get($userId)
    {
        try {
            $cacheKey = "customer_notifications_user_{$userId}";
            $notifications = Cache::get($cacheKey, []);

            return array_map(function ($notif) {
                if (isset($notif['timestamp'])) {
                    $notif['time'] = self::formatTimeAgo($notif['timestamp']);
                }
                return $notif;
            }, $notifications);
        } catch (\Exception $e) {
            Log::error('❌ Failed to get customer notifications', [
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
            $cacheKey = "customer_notifications_user_{$userId}";
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
    public static function getUnreadCount($userId)
    {
        $notifications = self::get($userId);
        return count(array_filter($notifications, fn($n) => !$n['is_read']));
    }

    /**
     * Clear all
     */
    public static function clear($userId)
    {
        Cache::forget("customer_notifications_user_{$userId}");
        Cache::forget("customer_notifications_unread_{$userId}");
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
     * 🔔 Notify: Reminder Pembayaran (Pending Payment)
     */
    public static function notifyPendingPayment($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'pending_payment',
                'title' => 'Menunggu Pembayaran',
                'description' => "Pesanan {$order->productRental->product->name} #{$order->order_code} menunggu pembayaran",
                'url' => route('customer.order.payment', $order->id)
            ]);

            // WA Notification
            $message = "*📋 REMINDER PEMBAYARAN*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pesanan Anda telah dibuat!\n\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━\n";
            $message .= " Kode Order: *{$order->order_code}*\n";
            $message .= "🏪 Toko: *{$order->productRental->product->shop->name_store}*\n";
            $message .= " Produk: *{$order->productRental->product->name}*\n";
            $message .= " Durasi: *{$order->productRental->cycle_value} Jam*\n";
            $message .= "💰 Total: *Rp " . number_format($order->total_amount, 0, ',', '.') . "*\n";
            $message .= " Metode: *" . ucfirst($order->delivery_method) . "*\n";
            $message .= "📅 Waktu Mulai: *" . Carbon::parse($order->start_time)->format('d/m/Y H:i') . "*\n";
            $message .= "━━━━━━━━━━━━━━\n\n";
            $message .= "⚠ *Segera selesaikan pembayaran Anda!*\n\n";
            $message .= "Akses link pembayaran:\n";
            $message .= route('customer.order.payment', $order->id) . "\n\n";
            $message .= "Terima kasih! 🙏";

            self::sendWa($order->user, $message);

            Log::info('✅ Pending payment notification added', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify pending payment', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Pembayaran Berhasil
     */
    public static function notifyPaymentSuccess($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            $title = 'Pembayaran Berhasil! ✅';
            $description = "Pembayaran pesanan {$order->productRental->product->name} #{$order->order_code} berhasil diproses";

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'payment_success',
                'title' => $title,
                'description' => $description,
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA Notification removed here as it is handled in Controller
            // self::sendWa($order->user, $message);

            Log::info('✅ Payment success notification added', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify payment success', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Pesanan Dalam Perjalanan
     */
    public static function notifyOrderInTransit($order, $courier = null)
    {
        try {
            if (!$order->relationLoaded('productRental.product')) {
                $order->load('productRental.product');
            }

            $courierName = $courier ? $courier->user->name : 'Kurir';

            $title = 'Pesanan Dalam Perjalanan 🚚';
            $description = "{$courierName} sedang mengantar {$order->productRental->product->name} #{$order->order_code}";

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'in_transit',
                'title' => $title,
                'description' => $description,
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA IS HANDLED BY CourierNotificationHelper::notifyAcceptance already
            // Duplicate message might be annoying, but ensure we cover cases where this might be called independently

        } catch (\Exception $e) {
            Log::error('Failed to notify in-transit', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Kurir Sudah Sampai
     */
    public static function notifyOrderArrived($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product')) {
                $order->load(['productRental.product', 'user']);
            }

            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            $title = 'Kurir Sudah Sampai! 📍';
            $description = "Kurir telah tiba di lokasi pengiriman untuk pesanan #{$order->order_code}.";

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'arrived',
                'title' => $title,
                'description' => $description,
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA Notification
            $message = "*📍 KURIR SUDAH SAMPAI*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Kurir telah tiba di lokasi pengiriman! 🙏\n\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= " Kode Order: *{$order->order_code}*\n";
            $message .= " Toko: *{$order->productRental->product->shop->name_store}*\n";
            $message .= " Produk: *{$order->productRental->product->name}*\n";
            $message .= "━━━━━━━━━━━━━━━\n\n";
            $message .= "Silakan temui kurir untuk menerima pesanan Anda. Jangan lupa siapkan QR Code atau OTP jika diperlukan. 😊\n\n";
            $message .= "Terima kasih!";

            self::sendWa($order->user, $message);

        } catch (\Exception $e) {
            Log::error('Failed to notify arrived', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Pesanan Selesai
     */
    public static function notifyOrderCompleted($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product')) {
                $order->load(['productRental.product', 'user']);
            }

            $title = 'Pesanan Selesai ✅';
            $description = "Pesanan {$order->productRental->product->name} #{$order->order_code} telah selesai. Terima kasih!";

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'completed',
                'title' => $title,
                'description' => $description,
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA Notification
            $message = "*✅ PESANAN SELESAI*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pesanan #{$order->order_code} telah selesai.\n";
            $message .= "Terima kasih telah menggunakan layanan kami! 🙏";

            self::sendWa($order->user, $message);

        } catch (\Exception $e) {
            Log::error('Failed to notify completed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Pesanan Dibatalkan
     */
    public static function notifyOrderCancelled($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product')) {
                $order->load(['productRental.product', 'user']);
            }

            $title = 'Pesanan Dibatalkan ❌';
            $description = "Pesanan {$order->productRental->product->name} #{$order->order_code} telah dibatalkan";

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'cancelled',
                'title' => $title,
                'description' => $description,
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA Notification
            $message = "*❌ PESANAN DIBATALKAN*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pesanan #{$order->order_code} telah dibatalkan.\n";
            $message .= "Jika Anda merasa ini adalah kesalahan, silakan hubungi kami.";

            self::sendWa($order->user, $message);

        } catch (\Exception $e) {
            Log::error('Failed to notify cancelled', [
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * 🔔 Notify: Customer mendekati toko (Pickup)
     */
    public static function notifyNearShop($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product')) {
                $order->load(['productRental.product', 'user']);
            }

            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'near_destination',
                'title' => 'Anda Sudah Dekat! 📍',
                'description' => "Sebentar lagi sampai di lokasi toko untuk pengambilan barang #{$order->order_code}",
                'url' => route('customer.order.show', $order->id)
            ]);

            // WA Notification
            $message = "*📍 ANDA SUDAH SAMPAI?*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Sistem mendeteksi posisi Anda sudah sangat dekat dengan lokasi toko.\n\n";
            $message .= "Silakan tekan tombol *'Sudah Sampai'* di halaman detail pesanan jika Anda sudah tiba di lokasi.\n\n";
            $message .= "🔗 Buka Pesanan:\n";
            $message .= route('customer.order.show', $order->id) . "\n\n";
            $message .= "Terima kasih!";

            self::sendWa($order->user, $message);

        } catch (\Exception $e) {
            Log::error('Failed to notify near shop', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔔 Notify: Penalty (Late Return)
     */
    public static function notifyPenalty($order, $lateFee, $overdueHours)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            // 1. In-App Notification
            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'penalty',
                'title' => 'Denda Keterlambatan ⚠️',
                'description' => "Keterlambatan {$overdueHours} jam. Denda: Rp " . number_format($lateFee, 0, ',', '.'),
                'url' => route('customer.order.show', $order->id) // Assuming they can pay from detail page
            ]);

            // 2. WhatsApp Notification
            $message = "*⚠️ NOTIFIKASI DENDA KETERLAMBATAN*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pengembalian barang untuk pesanan Anda tercatat terlambat.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL DENDA*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "⏰ Terlambat: *{$overdueHours} Jam*\n";
            $message .= "💸 Nominal Denda: *Rp " . number_format($lateFee, 0, ',', '.') . "*\n\n";
            $message .= "❗ *Status Pembayaran Denda: BELUM LUNAS*\n\n";
            $message .= "Mohon segera selesaikan pembayaran denda agar jaminan sewa (KTP) dapat dikembalikan.\n\n";
            $message .= "🔗 Bayar di sini:\n";
            $message .= route('customer.order.show', $order->id) . "\n\n";
            $message .= "Terima kasih atas perhatiannya. 🙏";

            self::sendWa($order->user, $message);

            Log::info('✅ Penalty notification sent', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'late_fee' => $lateFee
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify penalty', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Penalty Paid
     */
    public static function notifyPenaltyPaid($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            // 1. In-App Notification
            self::add($order->user_id, [
                'type' => 'order',
                'subtype' => 'penalty_paid',
                'title' => 'Pembayaran Denda Berhasil ✅',
                'description' => "Terima kasih, denda untuk pesanan #{$order->order_code} telah lunas.",
                'url' => route('customer.order.show', $order->id)
            ]);

            // 2. WhatsApp Notification
            $message = "*✅ PEMBAYARAN DENDA BERHASIL*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Pembayaran denda keterlambatan Anda telah berhasil diverifikasi.\n\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "📋 Kode Order: *{$order->order_code}*\n";
            $message .= "📦 Produk: *{$order->productRental->product->name}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "Kami telah selesai memproses pengembalian barang Anda.\n";
            $message .= "Jaminan sewa (KTP) Anda aman untuk diambil kembali.\n\n";
            $message .= "Terima kasih atas tanggung jawab Anda! 🙏";

            self::sendWa($order->user, $message);

            Log::info('✅ Penalty paid notification sent', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify penalty paid', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔔 Notify: Pengajuan Seller Submitted
     */
    public static function notifySellerRequestSubmitted($user)
    {
        try {
            self::add($user->id, [
                'type' => 'seller_request',
                'subtype' => 'submitted',
                'title' => 'Pengajuan Seller Diterima 📝',
                'description' => 'Pengajuan Anda sedang diverifikasi admin.',
                'url' => route('seller-request.my')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller request submitted', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔔 Notify: Pengajuan Seller Approved
     */
    public static function notifySellerRequestApproved($user)
    {
        try {
            self::add($user->id, [
                'type' => 'seller_request',
                'subtype' => 'approved',
                'title' => 'Pengajuan Seller Disetujui! 🎉',
                'description' => 'Selamat! Anda sekarang bisa mulai berjualan.',
                'url' => route('seller.dashboard.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller request approved', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔔 Notify: Pengajuan Seller Rejected
     */
    public static function notifySellerRequestRejected($user, $reason)
    {
        try {
            self::add($user->id, [
                'type' => 'seller_request',
                'subtype' => 'rejected',
                'title' => 'Pengajuan Seller Ditolak ❌',
                'description' => 'Alasan: ' . Str::limit($reason, 50),
                'url' => route('seller-request.my')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify seller request rejected', ['error' => $e->getMessage()]);
        }
    }
    /**
     * 🔔 Notify: Customer has picked up order (Thanks Card)
     */
    public static function notifyOrderPickedUp($order)
    {
        try {
            if (!$order->relationLoaded('productRental.product.shop')) {
                $order->load(['productRental.product.shop', 'user']);
            }

            // WA Notification
            $message = "*💝 TERIMA KASIH*\n\n";
            $message .= "Halo *{$order->user->name}*,\n\n";
            $message .= "Terima kasih sudah menyewa di toko kami! 🙏\n\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "*DETAIL PESANAN*\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= " Kode Order: *{$order->order_code}*\n";
            $message .= " Toko: *{$order->productRental->product->shop->name_store}*\n";
            $message .= " Produk: *{$order->productRental->product->name}*\n";
            $message .= "━━━━━━━━━━━━━━━\n\n";
            $message .= "Kami harap Anda puas dengan layanan kami. Selamat menggunakan barang sewaan Anda! 😊";

            self::sendWa($order->user, $message);

            Log::info('✅ Order picked up thanks message sent', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify order picked up', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔔 Notify: Admin for new Seller Request
     */
    public static function notifyAdminSellerRequest($user)
    {
        try {
            // Find all admins
            $admins = \App\Models\User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                if (empty($admin->phone)) continue;

                $message = "*🔔 NOTIFIKASI ADMIN: PENGAJUAN SELLER BARU*\n\n";
                $message .= "Halo *{$admin->name}*,\n\n";
                $message .= "Ada pengajuan baru dari customer yang ingin menjadi seller.\n\n";
                $message .= "━━━━━━━━━━━━━━━\n";
                $message .= "*DETAIL CUSTOMER*\n";
                $message .= "━━━━━━━━━━━━━━━\n";
                $message .= " Nama: *{$user->name}*\n";
                $message .= " Email: *{$user->email}*\n";
                $message .= " Phone: *{$user->phone}*\n";
                $message .= "━━━━━━━━━━━━━━━\n\n";
                $message .= "Mohon segera cek dashboard admin untuk proses verifikasi.\n\n";
                $message .= "Terima kasih!";

                self::sendWa($admin, $message);
            }

            Log::info('✅ Admin notified for new seller request', [
                'customer_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admin for seller request', ['error' => $e->getMessage()]);
        }
    }
}
