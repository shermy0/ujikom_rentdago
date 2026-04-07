<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * ShopMapController — Menampilkan peta lokasi seluruh toko seller kepada Admin.
 *
 * Mengambil koordinat toko yang valid dari database,
 * lalu memformat data agar siap digunakan oleh library peta di frontend (JavaScript).
 */
class ShopMapController extends Controller
{
    /**
     * Menampilkan peta dengan semua toko seller yang memiliki koordinat terdaftar.
     *
     * Hanya menampilkan toko yang latitude dan longitude-nya tidak null dan tidak 0.
     * Data diformat menjadi array ringkas untuk keperluan rendering peta di JavaScript.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil semua toko yang memiliki koordinat valid
        // Filter: latitude/longitude tidak null dan tidak 0
        $shops = Shop::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->select([
                'id',
                'name_store',
                'address_store',
                'latitude',
                'longitude',
                'is_active',
                'slug',
                'logo'
            ])
            ->get();

        // Format data untuk keperluan map javascript
        $mapData = $shops->map(function ($shop) {
            return [
                'id' => $shop->id,
                'name' => $shop->name_store,
                'address' => $shop->address_store,
                'latitude' => (float) $shop->latitude,
                'longitude' => (float) $shop->longitude,
                'is_active' => (bool) $shop->is_active,
                'detail_url' => route('admin.shops.show', $shop->id),
                'logo_url' => $shop->logo ? asset('storage/' . $shop->logo) : null,
            ];
        });

        return view('admin.shops.map', [
            'shops' => $mapData,
            'total' => $shops->count(),
        ])->with('title', 'Peta Toko Seller');
    }
}
