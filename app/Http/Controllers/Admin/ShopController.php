<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ShopController extends Controller
{
    /**
     * Display a listing of shops.
     */
    public function index(Request $request)
    {
        $query = Shop::with('user');

        // Search by name or address
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_store', 'like', "%{$search}%")
                  ->orWhere('address_store', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $shops = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = [
            'title' => 'Data Toko',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Toko', 'url' => '#'],
            ],
            'shops' => $shops,
        ];

        return view('admin.shops.index', $data);
    }


    /**
     * Display the specified shop.
     */
    public function show(Shop $shop)
    {
        $shop->load('user');

        $data = [
            'title' => 'Detail Toko',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Toko', 'url' => route('admin.shops.index')],
                ['title' => 'Detail Toko', 'url' => '#'],
            ],
            'shop' => $shop,
        ];

        return view('admin.shops.show', $data);
    }


    /**
     * Toggle Status Toko (Aktif/Nonaktif) - By Admin
     */
public function toggleStatus(Shop $shop)
{
    try {
        $shop->is_active = !$shop->is_active;
        
        // Jika admin menonaktifkan, tandai sebagai deactivated_by admin
        if (!$shop->is_active) {
            $shop->deactivated_by = 'admin';
        } else {
            // Jika admin mengaktifkan kembali, reset tracking
            $shop->deactivated_by = null;
        }
        
        $shop->save();

        $status = $shop->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('admin.shops.index')
            ->with('success', "Toko {$shop->name_store} berhasil {$status}");
            
    } catch (\Exception $e) {
        return redirect()->route('admin.shops.index')
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}
