<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Show the form for creating a new shop.
     */
    public function create()
    {
        // Get sellers without shop (for dropdown)
        $users = User::whereDoesntHave('shop')
            ->where('role', 'seller')
            ->orderBy('name')
            ->get();

        $data = [
            'title' => 'Tambah Toko',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Toko', 'url' => route('admin.shops.index')],
                ['title' => 'Tambah Toko', 'url' => '#'],
            ],
            'users' => $users,
        ];

        return view('admin.shops.create', $data);
    }

    /**
     * Store a newly created shop in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:shops,user_id',
            'name_store' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address_store' => 'required|string',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($validated['name_store']) . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs('shops', $logoName, 'public');
            $validated['logo'] = 'shops/' . $logoName;
        }

        $validated['is_active'] = $request->has('is_active');

        Shop::create($validated);

        return redirect()->route('admin.shops.index')
            ->with('success', 'Toko berhasil ditambahkan.');
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
