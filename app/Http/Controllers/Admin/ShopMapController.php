<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopMapController extends Controller
{
    /**
     * Display shop map with all registered seller locations
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
