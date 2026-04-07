<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ShopController — Mengelola data toko oleh Admin.
 *
 * Menyediakan fitur tampilan daftar toko dengan pencarian dan filter status,
 * melihat detail toko, serta mengaktifkan atau menonaktifkan toko secara manual.
 * Penambahan toko dilakukan oleh seller sendiri melalui menu MyPage.
 */
class ShopController extends Controller
{
    /**
     * Menampilkan daftar seluruh toko yang terdaftar di platform.
     *
     * Mendukung pencarian berdasarkan nama atau alamat toko,
     * serta filter berdasarkan status aktif/nonaktif.
     * Hasil ditampilkan dengan pagination 10 data per halaman.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil semua toko beserta data pemilik (user)
        $query = Shop::with('user');

        // Pencarian berdasarkan nama toko atau alamat toko
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_store', 'like', "%{$search}%")
                  ->orWhere('address_store', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status toko (aktif atau nonaktif)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Urutkan dari terbaru dan ambil dengan pagination
        $shops = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = [
            'title'       => 'Data Toko',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Toko', 'url' => '#'],
            ],
            'shops' => $shops,
        ];

        return view('admin.shops.index', $data);
    }

    /**
     * Menampilkan halaman detail sebuah toko.
     *
     * Memuat data pemilik toko (relasi user) secara eager loading.
     *
     * @param \App\Models\Shop $shop
     * @return \Illuminate\View\View
     */
    public function show(Shop $shop)
    {
        // Muat relasi pemilik toko
        $shop->load('user');

        $data = [
            'title'       => 'Detail Toko',
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
     * Mengubah status aktif/nonaktif sebuah toko oleh Admin.
     *
     * Jika admin menonaktifkan toko, kolom `deactivated_by` diisi 'admin'
     * sebagai catatan siapa yang menonaktifkan.
     * Jika admin mengaktifkan kembali, kolom tersebut direset ke null.
     *
     * @param \App\Models\Shop $shop
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Shop $shop)
    {
        try {
            // Balik nilai status toko (aktif → nonaktif, atau sebaliknya)
            $shop->is_active = !$shop->is_active;

            if (!$shop->is_active) {
                // Tandai bahwa penonaktifan dilakukan oleh admin
                $shop->deactivated_by = 'admin';
            } else {
                // Reset tracking saat admin mengaktifkan kembali toko
                $shop->deactivated_by = null;
            }

            $shop->save();

            $status = $shop->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->route('admin.shops.index')
                ->with('success', "Toko {$shop->name_store} berhasil {$status}");

        } catch (\Exception $e) {
            // Tangani error tak terduga dan tampilkan pesan ke pengguna
            return redirect()->route('admin.shops.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
