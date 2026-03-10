<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderHandoverProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScanQRController extends Controller
{
    /**
     * ===========================
     * HALAMAN UTAMA SCAN QR
     * ===========================
     * Hanya menampilkan view kamera scanner
     */
    public function index()
    {
        return view('seller.scan.index')->with('title', 'Scan QR Pick Up');;
    }

    /**
     * ======================================================
     * CEK KETERSEDIAAN BARANG FISIK
     * ======================================================
     * Barang dianggap TIDAK tersedia jika:
     * - Ada order lain
     * - Dengan product_rental_id yang sama
     * - Statusnya masih ongoing
     * - Bukan order yang sedang discan sekarang
     * Kalau barang fisik SUDAH tersedia,
     * maka pickup boleh dilakukan kapan pun
     */
    private function isItemAvailable(Order $order): bool
    {
        return !Order::where('product_rental_id', $order->product_rental_id)
            ->where('status', 'ongoing')
            ->where('id', '!=', $order->id)
            ->exists();
    }

    /**
     * ======================================================
     * STEP 1 — VERIFIKASI QR CODE
     * ======================================================
     * - Dipanggil saat QR discan
     * - HANYA validasi & ambil data
     * - TIDAK mengubah status order
     */
    public function verify(Request $request)
    {
        // Validasi input QR
        $request->validate([
            'order_code' => 'required|string'
        ]);

        // Cari order + relasi
        $order = $this->findOrder($request->order_code);

        if (!$order) {
            return $this->errorResponse('Order tidak ditemukan', 404);
        }

        /**
         * ===========================
         * VALIDASI DASAR ORDER
         * ===========================
         * - Harus pickup
         * - Status harus confirmed / ongoing
         * - Pembayaran harus paid
         */
        if ($validation = $this->validateOrderBasics($order)) {
            return $validation;
        }

        /**
         * ===========================
         * VALIDASI KEPEMILIKAN TOKO
         * ===========================
         * Pastikan QR ini memang milik toko seller yang login
         */
        if ($ownershipValidation = $this->validateShopOwnership($order)) {
            return $ownershipValidation;
        }

        /**
         * ===========================
         * TENTUKAN AKSI
         * ===========================
         * - confirmed  → start (serah barang)
         * - ongoing    → return (terima kembali)
         */
        $isAvailable = $this->isItemAvailable($order);

        $actionType = $order->status === 'ongoing'
            ? 'return'
            : 'start';

        /**
         * ===========================
         * VALIDASI WAKTU PICKUP
         * ===========================
         * Hanya dicek jika mau START
         */
        if ($actionType === 'start') {
            if ($timeValidation = $this->validateStartTime($order)) {
                return $timeValidation;
            }
        }

        /**
         * ===========================
         * AMBIL FOTO PRODUK
         * ===========================
         * - Prioritas: gambar utama (is_primary)
         * - Fallback: gambar pertama
         * - Kalau tidak ada → gambar default
         */
        $product = $order->productRental->product;

        $mainImage = $product->images
            ->where('is_primary', true)
            ->first()
            ?? $product->images->first();

        $imageUrl = $mainImage
            ? asset('storage/' . $mainImage->image_path)
            : asset('images/no-image.png');

        /**
         * ===========================
         * RESPONSE KE FRONTEND
         * ===========================
         * Data ini dipakai SweetAlert
         */
        return $this->successResponse('QR Code valid', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'customer_name' => $order->user->name,
            'product_name' => $product->name,
            'product_image' => $imageUrl,

            'scheduled_start_time' => $this->formatDateTime($order->scheduled_start_time),
            'start_time' => $this->formatDateTime($order->start_time),
            'end_time' => $this->formatDateTime($order->end_time),

            'action_type' => $actionType,
            'can_start' => $order->status === 'confirmed',
            'can_return' => $order->status === 'ongoing',
            'item_available' => $isAvailable,
        ]);
    }

    /**
     * ======================================================
     * STEP 3 — MULAI RENTAL (CONFIRMED → ONGOING)
     * ======================================================
     * Dipanggil setelah seller klik "Serahkan Barang"
     */
    public function start(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::with('productRental')->findOrFail($request->order_id);

        // Pastikan order milik toko seller
        if ($response = $this->validateShopOwnership($order)) {
            return $response;
        }

        // Status harus confirmed
        if ($order->status !== 'confirmed') {
            return $this->errorResponse('Order tidak bisa dimulai', 400);
        }

// CEK FOTO SERAH BARANG
$hasStartPhoto = OrderHandoverProof::where('order_id', $order->id)->exists();

if (!$hasStartPhoto) {
    return $this->errorResponse(
        'Foto serah barang wajib diambil sebelum memulai rental',
        422
    );
}


        $now = Carbon::now();
        $rentalHours = (int) $order->productRental->cycle_value;

        /**
         * ===========================
         * LOGIKA KOMPENSASI
         * ===========================
         * Kalau customer datang tepat waktu
         * tapi barang baru tersedia sekarang
         */
        $startTime = $now;
        $isCompensated = false;

        if ($order->scheduled_start_time) {
            if ($now->gt(Carbon::parse($order->scheduled_start_time))) {
                $isCompensated = true;
            }
        }

        // Update status & waktu
        $order->update([
            'status'     => 'ongoing',
            'start_time' => $startTime,
            'end_time'   => $startTime->copy()->addHours($rentalHours),
        ]);

        return $this->successResponse(
            'Rental berhasil dimulai',
            [
                'compensated' => $isCompensated,
                'new_start'   => $startTime->format('d/m/Y H:i')
            ]
        );
    }

    public function uploadStartProof(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'photo'    => 'required|image|max:2048',
    ]);

    $order = Order::findOrFail($request->order_id);

    // Pastikan milik toko seller
    if ($response = $this->validateShopOwnership($order)) {
        return $response;
    }

    if ($order->status !== 'confirmed') {
        return $this->errorResponse('Order tidak dalam status confirmed', 400);
    }

    $path = $request->file('photo')->store('handover/start', 'public');

    OrderHandoverProof::create([
        'order_id'  => $order->id,
        'type'      => 'start',
        'photo_path'=> $path,
        'taken_by'  => 'seller',
    ]);

    return $this->successResponse('Foto serah barang berhasil disimpan');
}

    /**
     * ======================================================
     * STEP 3 — TERIMA PENGEMBALIAN BARANG
     * ======================================================
     * - Hitung keterlambatan
     * - Buat denda jika perlu
     * - Update status order
     */
    public function returnItem(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::with(['productRental.product'])->findOrFail($request->order_id);

        if ($response = $this->validateShopOwnership($order)) {
            return $response;
        }

        if ($order->status !== 'ongoing') {
            return $this->errorResponse('Order tidak dalam status ongoing', 400);
        }

        try {
            return DB::transaction(function () use ($order) {

                $now = Carbon::now();
                $scheduledEnd = Carbon::parse($order->end_time);

                $isOverdue = $now->gt($scheduledEnd);
                $lateFee = 0;
                $overdueHours = 0;

                /**
                 * ===========================
                 * JIKA TERLAMBAT
                 * ===========================
                 */
                if ($isOverdue) {
                    $overdueMinutes = $scheduledEnd->diffInMinutes($now);
                    $overdueHours = ceil($overdueMinutes / 60);

                    $rental = $order->productRental;

                    $lateCycles = ceil(
                        $overdueMinutes / ($rental->penalties_cycle_value * 60)
                    );

                    $lateFee = $lateCycles * $rental->penalties_price;

                    // Simpan data denda
                    OrderReturn::create([
                        'order_id'         => $order->id,
                        'returned_at'      => $now,
                        'penalties_amount' => $lateFee,
                        'payment_status'   => 'unpaid',
                    ]);

                    $order->update([
                        'status'      => 'penalty',
                        'returned_at' => $now,
                    ]);

                    // 🔥 NEW: Trigger Penalty Notification
                    \App\Helpers\CustomerNotificationHelper::notifyPenalty($order, $lateFee, $overdueHours);
                } 
                /**
                 * ===========================
                 * JIKA TEPAT WAKTU
                 * ===========================
                 */
                else {
                    $order->update([
                        'status'      => 'completed',
                        'returned_at' => $now,
                    ]);
                }

                return $this->successResponse(
                    $isOverdue
                        ? 'Barang berhasil dikembalikan, namun melewati batas waktu sewa. Denda menunggu pembayaran. Jaminan sewa (KTP) dapat dikembalikan setelah denda dilunasi.'
                        : 'Barang dikembalikan tepat waktu',
                    [
                        'is_overdue'    => $isOverdue,
                        'late_fee'      => $lateFee,
                        'overdue_hours' => $overdueHours,
                    ]
                );
            });
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Gagal memproses pengembalian: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * ======================================================
     * HELPER METHODS
     * ======================================================
     */

    /**
     * Cari order + relasi lengkap
     */
    private function findOrder(string $orderCode): ?Order
    {
        return Order::with([
            'productRental.product.images',
            'user'
        ])
        ->where('order_code', $orderCode)
        ->first();
    }

    /**
     * Validasi dasar order
     */
    private function validateOrderBasics(Order $order): ?JsonResponse
    {
        if ($order->delivery_method !== 'pickup') {
            return $this->errorResponse('QR Code hanya untuk metode pickup', 400);
        }

        if (!in_array($order->status, ['confirmed', 'ongoing'])) {
            return $this->errorResponse(
                'Status order tidak valid untuk scan. Status: ' . $order->status,
                400
            );
        }

        if ($order->payment_status !== 'paid') {
            return $this->errorResponse('Order belum dibayar', 400);
        }

        return null;
    }

    /**
     * Validasi kepemilikan toko seller
     */
    private function validateShopOwnership(Order $order): ?JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== 'seller' || !$user->shop) {
            return $this->errorResponse('Akses ditolak', 403);
        }

        if ((int) $order->productRental->product->shop_id !== (int) $user->shop->id) {
            return $this->errorResponse('QR ini bukan milik toko Anda', 403);
        }

        return null;
    }

/**
 * Validasi waktu mulai rental
 * Dipanggil saat QR discan & action = START (serah barang)
 */
private function validateStartTime(Order $order): ?JsonResponse
{
        $now = Carbon::now();

    /**
     * ===========================
     * RULE KADALUARSA
     * ===========================
     * Kalau end_time sudah lewat dari sekarang
     * → order dianggap hangus / expired
     */
    if ($order->end_time && $now->gt(Carbon::parse($order->end_time))) {
        return $this->errorResponse(
            'Waktu pickup sudah berakhir',
            410, // Gone
            [
                'expired' => true,
                'end_time' => Carbon::parse($order->end_time)->format('d/m/Y H:i')
            ]
        );
    }
    /**
     * RULE UTAMA:
     * Kalau barang fisik SUDAH tersedia,
     * maka pickup boleh dilakukan kapan pun
     * (tidak peduli jadwal).
     */
    if ($this->isItemAvailable($order)) {
        return null; // valid → lanjut proses
    }

    /**
     * Kalau barang BELUM tersedia,
     * berarti masih dipakai order lain
     * → kita perlu cek jadwal.
     */
    $scheduledTime = $order->scheduled_start_time ?? $order->start_time;

    /**
     * Kalau tidak ada jadwal sama sekali,
     * sistem tidak bisa memblok → dianggap aman
     */
    if (!$scheduledTime) {
        return null;
    }

    $now = Carbon::now();
    $scheduledStartTime = Carbon::parse($scheduledTime);

    /**
     * KASUS 1:
     * Customer datang TEPAT WAKTU atau SUDAH lewat jadwal
     * tapi barang masih dipakai order sebelumnya
     * update start  time sekarang, dan endtimenya sesuai durasi yg ada di paket_rental
     */
    if ($now->gte($scheduledStartTime)) {
        return $this->errorResponse(
            'Barang belum tersedia karena masih dipakai pada pesanan sebelumnya',
            409, // Conflict
            [
                // Dipakai frontend untuk tampilkan info khusus
                'item_unavailable' => true,
                'scheduled_time'   => $scheduledStartTime->format('d/m/Y H:i')
            ]
        );
    }

    /**
     * KASUS 2:
     * Customer datang TERLALU CEPAT
     * dan barang juga belum tersedia
     */
    return $this->errorResponse(
        'Belum waktunya pickup dan barang belum tersedia',
        400
    );
}

/**
     * Format waktu tersisa menjadi "X jam Y menit"
     */
    private function formatTimeRemaining(int $totalMinutes): string
    {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0) {
            return "{$hours} jam" . ($minutes > 0 ? " {$minutes} menit" : "");
        }

        return "{$minutes} menit";
    }

    /**
     * Format datetime dengan fallback
     */
    private function formatDateTime($datetime): string
    {
        if (!$datetime) {
            return '-';
        }

        return Carbon::parse($datetime)->format('d/m/Y H:i');
    }

    /**
     * Response sukses konsisten
     */
    private function successResponse(string $message, array $data = []): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }

    /**
     * Response error konsisten
     */
    private function errorResponse(string $message, int $statusCode = 400, array $extraData = []): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        // Merge extra data untuk kasus khusus (misal: time_remaining)
        if (!empty($extraData)) {
            $response = array_merge($response, $extraData);
        }

        return response()->json($response, $statusCode);
    }
}