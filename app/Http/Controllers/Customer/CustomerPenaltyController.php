<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CustomerPenaltyController — Mengelola pembayaran denda keterlambatan oleh Customer.
 *
 * Menangani alur pembayaran denda (OrderReturn dengan payment_status 'unpaid')
 * menggunakan Midtrans Snap. Juga menyediakan endpoint simulasi callback
 * Midtrans khusus untuk keperluan pengujian di environment lokal.
 */
class CustomerPenaltyController extends Controller
{
    /**
     * Menampilkan halaman pembayaran denda keterlambatan pengembalian.
     *
     * Mengatur konfigurasi Midtrans, membuat parameter transaksi
     * berdasarkan data denda (OrderReturn), mengambil Snap Token dari Midtrans,
     * lalu menyimpan midtrans_order_id unik ke database sebelum menampilkan view.
     *
     * @param int $orderReturnId ID denda (order_returns) yang akan dibayar
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pay($orderReturnId)
{
    $penalty = OrderReturn::with('order.user')->findOrFail($orderReturnId);

    if ($penalty->payment_status === 'paid') {
        return redirect()->back()->with('info', 'Denda sudah dibayar');
    }

    // 🔑 SET CONFIG MIDTRANS (WAJIB)
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$isSanitized = true;
    Config::$is3ds = true;

    $midtransOrderId = 'PENALTY-' . $penalty->id . '-' . time();

    $params = [
        'transaction_details' => [
            'order_id' => $midtransOrderId,
            'gross_amount' => $penalty->penalties_amount,
        ],
        'item_details' => [[
            'id' => 'penalty-' . $penalty->id,
            'price' => $penalty->penalties_amount,
            'quantity' => 1,
            'name' => 'Denda Keterlambatan Pengembalian',
        ]],
        'customer_details' => [
            'first_name' => $penalty->order->user->name,
            'email' => $penalty->order->user->email,
        ],
    ];

    $snapToken = Snap::getSnapToken($params);

    $penalty->update([
        'midtrans_order_id' => $midtransOrderId
    ]);

    return view('frontend.order.penalty-payment', compact('penalty', 'snapToken'));
}


    /**
     * Mensimulasikan callback Midtrans untuk pengujian pembayaran denda di lokal.
     *
     * ⚠️ HANYA UNTUK ENVIRONMENT LOKAL / TESTING — JANGAN DIPAKAI DI PRODUCTION.
     * Memproses simulasi status transaksi settlement/capture sebagai pembayaran berhasil,
     * mengupdate status denda menjadi 'paid', dan menyelesaikan pesanan terkait.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulateMidtransCallback(Request $request)
{
    /**
     * ⚠️ KHUSUS LOCAL / TESTING
     * JANGAN DIPAKAI DI PRODUCTION
     */

    $request->validate([
        'midtrans_order_id' => 'required|string',
        'transaction_status' => 'required|in:settlement,capture,pending,deny,cancel,expire',
    ]);

    $transactionStatus = $request->transaction_status;
    $fraudStatus = $request->fraud_status ?? 'accept';
    $now = now();

    // =====================================================
    // 🔥 HANDLE PENALTY (ORDER_RETURN)
    // =====================================================
    if (Str::startsWith($request->midtrans_order_id, 'PENALTY-')) {

        $penalty = OrderReturn::with('order')
            ->where('midtrans_order_id', $request->midtrans_order_id)
            ->first();

        if (!$penalty) {
            return response()->json(['message' => 'Penalty not found'], 404);
        }

        if ($penalty->payment_status === 'paid') {
            return response()->json(['message' => 'Penalty already paid']);
        }

        if (
            $transactionStatus === 'settlement' ||
            ($transactionStatus === 'capture' && $fraudStatus === 'accept')
        ) {
            $penalty->update([
                'payment_status' => 'paid',
                'paid_at' => $now,
            ]);

            // HANYA SELESAIKAN ORDER
            $penalty->order->update([
                'status' => 'completed',
            ]);

            // 🔥 NEW: Trigger Penalty Paid Notification
            \App\Helpers\CustomerNotificationHelper::notifyPenaltyPaid($penalty->order);
        }

        return response()->json([
            'message' => 'Penalty simulated callback processed',
            'status' => $transactionStatus
        ]);
    }

    return response()->json(['message' => 'Invalid order type'], 400);
}

}