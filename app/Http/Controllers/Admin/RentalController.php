<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRental;
use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * RentalController — Mengelola data paket sewa produk (ProductRental) oleh Admin.
 *
 * Menyediakan tampilan baca-saja (read-only) atas seluruh paket sewa
 * yang didaftarkan oleh seller, lengkap dengan filter pencarian,
 * filter toko, dan filter metode pengiriman.
 */
class RentalController extends Controller
{
    /**
     * Menampilkan daftar seluruh paket sewa yang tersedia.
     *
     * Mendukung pencarian berdasarkan nama atau kode produk,
     * filter berdasarkan toko, dan filter berdasarkan metode pengiriman
     * (pickup, delivery, atau pickup_delivery).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ProductRental::with([
            'product.category',
            'product.images',
            'product.shop',
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by shop
        if ($request->filled('shop')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('shop_id', $request->shop);
            });
        }

        // Filter by delivery type
        if ($request->filled('delivery')) {
            $delivery = $request->delivery;
            if ($delivery === 'pickup_delivery') {
                $query->where('is_delivery', 'pickup_delivery');
            } else {
                $query->where(function ($q) use ($delivery) {
                    $q->where('is_delivery', $delivery)
                        ->orWhere('is_delivery', 'pickup_delivery');
                });
            }
        }

        $rentals = $query->latest()->paginate(10);
        $shops = Shop::where('is_active', true)->orderBy('name_store')->get();

        return view('admin.product_sewa.index', [
            'title' => 'Produk Sewa',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Produk Sewa', 'url' => '#']
            ],
            'rentals' => $rentals,
            'shops' => $shops
        ]);
    }



    /**
     * Menampilkan halaman detail sebuah paket sewa.
     *
     * Memuat relasi kategori, gambar produk, dan informasi toko.
     *
     * @param int $id ID paket sewa (product_rentals)
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $rental = ProductRental::with(['product.category', 'product.images', 'product.shop'])
            ->findOrFail($id);

        return view('admin.product_sewa.show', [
            'title' => 'Detail Produk Sewa',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Produk Sewa', 'url' => route('admin.product_sewa.index')],
                ['title' => 'Detail', 'url' => '#']
            ],
            'rental' => $rental
        ]);
    }

}
