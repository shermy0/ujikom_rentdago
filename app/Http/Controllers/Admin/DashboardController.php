<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\SellerRequest;

/**
 * DashboardController — Mengelola halaman beranda Admin.
 *
 * Menampilkan ringkasan statistik aplikasi secara keseluruhan,
 * termasuk jumlah user, seller, produk, pesanan,
 * serta daftar pengajuan seller dan pesanan terbaru.
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard admin.
     *
     * Mengambil data statistik utama dan aktivitas terbaru
     * untuk ditampilkan sebagai ringkasan pada beranda admin.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.dashboard.index', [
            // Total seluruh pengguna terdaftar
            'totalUsers'    => User::count(),

            // Total pengguna dengan role seller
            'totalSellers'  => User::where('role', 'seller')->count(),

            // Total produk yang tersedia di platform
            'totalProducts' => Product::count(),

            // Total seluruh pesanan yang pernah masuk
            'totalOrders'   => Order::count(),

            // 5 pengajuan seller terbaru untuk ditampilkan di dashboard
            'latestSellerRequests' => SellerRequest::latest()->take(5)->get(),

            // 5 pesanan terbaru untuk ditampilkan di dashboard
            'latestOrders' => Order::latest()->take(5)->get(),
        ])->with('title', 'Dashboard Admin');
    }
}