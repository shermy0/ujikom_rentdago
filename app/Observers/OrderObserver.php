<?php

namespace App\Observers;

use App\Models\Order;
use App\Helpers\CustomerNotificationHelper;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Triggered saat order baru dibuat
     */
    public function created(Order $order)
    {
        try {
            // Hanya notify jika status pending dan payment unpaid
            if ($order->status === 'pending' && $order->payment_status === 'unpaid') {
                Log::info('🆕 Order created, sending pending payment notification', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'user_id' => $order->user_id
                ]);

                CustomerNotificationHelper::notifyPendingPayment($order);
            }
        } catch (\Exception $e) {
            Log::error('OrderObserver::created failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     * Triggered saat order di-update
     */
    public function updated(Order $order)
    {
        try {
            // Cek perubahan payment_status dari unpaid ke paid
            if ($order->isDirty('payment_status')) {
                $oldPaymentStatus = $order->getOriginal('payment_status');
                $newPaymentStatus = $order->payment_status;

                Log::info('💳 Payment status changed', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'from' => $oldPaymentStatus,
                    'to' => $newPaymentStatus
                ]);

                if ($oldPaymentStatus === 'unpaid' && $newPaymentStatus === 'paid') {
                    CustomerNotificationHelper::notifyPaymentSuccess($order);
                }
            }

            // Cek perubahan status order
            if ($order->isDirty('status')) {
                $oldStatus = $order->getOriginal('status');
                $newStatus = $order->status;

                Log::info('📊 Order status changed', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'from' => $oldStatus,
                    'to' => $newStatus
                ]);

                // Status: arrived (kurir sampai)
                if ($newStatus === 'arrived') {
                    CustomerNotificationHelper::notifyOrderArrived($order);
                }

                // Status: completed (pesanan selesai)
                if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                    CustomerNotificationHelper::notifyOrderCompleted($order);
                }

                // Status: cancelled (pesanan dibatalkan)
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    CustomerNotificationHelper::notifyOrderCancelled($order);
                }
            }

            // Cek jika tracking aktif (pesanan dalam perjalanan)
            if ($order->isDirty('is_tracking_active')) {
                $wasTracking = $order->getOriginal('is_tracking_active');
                $isTracking = $order->is_tracking_active;

                if (!$wasTracking && $isTracking) {
                    Log::info('🚚 Tracking activated', [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code
                    ]);

                    // Load courier jika ada
                    $courier = $order->courier ?? null;
                    CustomerNotificationHelper::notifyOrderInTransit($order, $courier);
                }
            }

        } catch (\Exception $e) {
            Log::error('OrderObserver::updated failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order)
    {
        // Optional: Clear notifications when order deleted
        // CustomerNotificationHelper::clearOrderNotifications($order->user_id, $order->id);
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order)
    {
        //
    }
}