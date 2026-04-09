<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * OrderController — Mengelola data pesanan oleh Admin.
 *
 * Menyediakan tampilan baca-saja (read-only) atas seluruh pesanan
 * yang masuk dari semua toko di platform, lengkap dengan filter dan statistik.
 */
class OrderController extends Controller
{
    /**
     * Menampilkan daftar seluruh pesanan dari semua toko.
     *
     * Mendukung filter berdasarkan status pesanan, status pembayaran,
     * dan pencarian berdasarkan kode pesanan atau nama customer.
     * Hasil diurutkan dari pesanan terbaru dan dipaginasi 15 data per halaman.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil semua pesanan beserta relasi yang dibutuhkan untuk tampilan
        $query = Order::with(['user', 'productRental.product.shop', 'payment']);

        // Filter berdasarkan status pesanan (pending, confirmed, ongoing, completed, dll)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan status pembayaran dari tabel payments
        if ($request->filled('payment_status')) {
            $query->whereHas('payment', fn($q) => $q->where('payment_status', $request->payment_status));
        }

        // Pencarian berdasarkan kode pesanan atau nama customer
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Urutkan dari terbaru dan ambil dengan pagination
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // Hitung statistik ringkasan untuk ditampilkan di header halaman
        $stats = [
            'total'     => Order::count(),
            'pending'   => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'ongoing'   => Order::where('status', 'ongoing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            // Statistik pembayaran dari tabel terpisah
            'paid'      => \App\Models\Payment::where('payment_status', 'paid')->count(),
            'unpaid'    => \App\Models\Payment::where('payment_status', 'unpaid')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'))->with('title', 'Data Pemesanan');
    }

    /**
     * Menampilkan halaman detail sebuah pesanan.
     *
     * Memuat relasi lengkap termasuk data customer, produk,
     * foto produk, dan informasi pembayaran.
     *
     * @param int $id ID pesanan
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Ambil detail pesanan beserta semua relasi yang diperlukan
        $order = Order::with([
            'user',                              // Data customer pemesan
            'productRental.product.shop',        // Produk dan toko asalnya
            'productRental.product.images',      // Foto-foto produk
            'payment',                           // Data pembayaran
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }
}
