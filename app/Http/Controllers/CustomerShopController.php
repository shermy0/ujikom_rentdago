<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class CustomerShopController extends Controller
{
    public function show($slug)
    {
        // Ambil data toko berdasarkan slug
        $shop = Shop::with(['products' => function($query) {
            $query->where('is_maintenance', false)
                  ->latest();
        }])
        ->where('slug', $slug)
        ->firstOrFail();

        // Hitung total produk
        $totalProducts = $shop->products->count();

        // Ambil voucher aktif dari toko ini (tanpa batasan usage_limit)
        $vouchers = $shop->activeVouchers()->get();

        // Jika user login, cek voucher mana yang sudah diklaim dan sudah digunakan
        $claimedVoucherIds = [];
        $usedVoucherIds = [];
        
        if (Auth::check()) {
            // Voucher yang sudah diklaim
            $claimedVoucherIds = Auth::user()->vouchers()
                ->where('shop_id', $shop->id)
                ->pluck('vouchers.id')
                ->toArray();

            // Voucher yang sudah digunakan (ada di voucher_usages)
            $usedVoucherIds = \DB::table('voucher_usages')
                ->join('vouchers', 'voucher_usages.voucher_id', '=', 'vouchers.id')
                ->where('voucher_usages.user_id', Auth::id())
                ->where('vouchers.shop_id', $shop->id)
                ->pluck('voucher_usages.voucher_id')
                ->toArray();
        }

        return view('frontend.shop.profile', compact(
            'shop', 
            'totalProducts', 
            'vouchers', 
            'claimedVoucherIds',
            'usedVoucherIds'
        ))->with('title', 'Profil Toko');
    }
}