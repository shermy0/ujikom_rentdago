<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class SellerProductController extends Controller
{
/**
 * ======================================================
 * MENAMPILKAN LIST PRODUK MILIK SELLER
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Mengambil semua produk milik toko seller yang login
 * - Mendukung fitur pencarian (search)
 * - Mendukung filter berdasarkan kategori dan status
 * - Menampilkan data dengan pagination
 */
public function index(Request $request)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Validasi: jika seller belum punya toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu sebelum mengelola produk.');
    }

    /**
     * ======================================================
     * QUERY DASAR PRODUK
     * ======================================================
     * - Ambil produk berdasarkan shop_id
     * - Load relasi category dan images (eager loading)
     *   agar lebih efisien (menghindari N+1 query)
     */
    $query = Product::with(['category', 'images'])
        ->where('shop_id', $shop->id);

    /**
     * ======================================================
     * FITUR SEARCH (PENCARIAN)
     * ======================================================
     * - Jika user mengisi input search
     * - Maka cari berdasarkan:
     *   → nama produk
     *   → kode produk
     */
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * ======================================================
     * FILTER BERDASARKAN KATEGORI
     * ======================================================
     * - Jika user memilih kategori tertentu
     * - Maka hanya tampilkan produk dari kategori tersebut
     */
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    /**
     * ======================================================
     * FILTER BERDASARKAN STATUS PRODUK
     * ======================================================
     * - available    → produk bisa disewa
     * - maintenance  → produk sedang tidak tersedia
     */
    if ($request->filled('status')) {
        if ($request->status === 'available') {
            $query->where('is_maintenance', 0);
        } elseif ($request->status === 'maintenance') {
            $query->where('is_maintenance', 1);
        }
    }

    /**
     * ======================================================
     * EKSEKUSI QUERY
     * ======================================================
     * - Urutkan dari terbaru (latest)
     * - Gunakan pagination (10 data per halaman)
     */
    $products   = $query->latest()->paginate(10);

    /**
     * ======================================================
     * AMBIL DATA KATEGORI
     * ======================================================
     * - Digunakan untuk dropdown filter di frontend
     */
    $categories = Category::orderBy('name')->get();

    // Kirim data ke view
    return view('seller.products.index', compact('products', 'categories'))
        ->with('title', 'Daftar Produk');
}


/**
 * ======================================================
 * MENAMPILKAN FORM TAMBAH PRODUK
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Menampilkan halaman form tambah produk
 * - Mengirim data kategori ke view (untuk dropdown)
 */
public function create()
{
    // Ambil semua kategori, diurutkan berdasarkan nama
    $categories = Category::orderBy('name')->get();

    // Tampilkan halaman form create
    return view('seller.products.create', compact('categories'))
        ->with('title', 'Tambah Produk');
}

/**
 * ======================================================
 * MENYIMPAN DATA PRODUK BARU
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Validasi input dari form
 * - Generate kode produk otomatis
 * - Menyimpan data produk ke database
 * - Upload dan simpan gambar produk
 * - Menggunakan transaction untuk menjaga konsistensi data
 */
public function store(Request $request)
{
    /**
     * ======================================================
     * VALIDASI INPUT
     * ======================================================
     * - Pastikan kategori valid
     * - Nama wajib diisi
     * - Gambar harus berupa file image (jpg/png)
     */
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'condition' => 'nullable|string',
        'is_maintenance' => 'boolean',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    // Mulai database transaction
    DB::beginTransaction();

    try {
        /**
         * ======================================================
         * GENERATE KODE PRODUK
         * ======================================================
         * - Kode dibuat otomatis berdasarkan kategori
         */
        $code = $this->generateProductCode($request->category_id);

        // Ambil toko milik user login
        $shop = Auth::user()->shop;

        /**
         * ======================================================
         * SIMPAN DATA PRODUK
         * ======================================================
         */
        $product = Product::create([
            'code' => $code,
            'category_id' => $request->category_id,
            'shop_id' => $shop->id,
            'name' => $request->name,
            'description' => $request->description,
            'condition' => $request->condition,
            'is_maintenance' => $request->boolean('is_maintenance')
        ]);

        /**
         * ======================================================
         * UPLOAD GAMBAR PRODUK
         * ======================================================
         * - Loop semua file gambar
         * - Simpan ke storage/public/products
         * - Simpan path ke database
         */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path
                ]);
            }
        }

        // Commit jika semua berhasil
        DB::commit();

        return redirect()->route('seller.products.index')
            ->with('success', 'Barang berhasil ditambahkan!');

    } catch (\Exception $e) {

        // Rollback jika terjadi error
        DB::rollBack();

        /**
         * ======================================================
         * HANDLE ERROR FILE
         * ======================================================
         * - Hapus file yang sudah terlanjur diupload
         * - Mencegah file sampah di storage
         */
        if (isset($product) && $product->images) {
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }
        }

        return back()->withInput()
            ->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
    }
}


/**
 * ======================================================
 * MENAMPILKAN FORM EDIT PRODUK
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Mengambil data produk berdasarkan ID
 * - Memastikan produk milik toko seller
 * - Menampilkan form edit dengan data produk
 */
public function edit($id)
{
    // Ambil toko milik user login
    $shop = Auth::user()->shop;

    /**
     * ======================================================
     * AMBIL DATA PRODUK
     * ======================================================
     * - Ambil berdasarkan ID
     * - Pastikan produk milik toko tersebut (security)
     * - Load relasi images untuk ditampilkan di form
     */
    $product = Product::with('images')
        ->where('shop_id', $shop->id)
        ->findOrFail($id);

    // Ambil data kategori untuk dropdown
    $categories = Category::orderBy('name')->get();

    // Tampilkan halaman edit
    return view('seller.products.edit', compact('product', 'categories'))
        ->with('title', 'Edit Produk');
}


/**
 * ======================================================
 * UPDATE DATA PRODUK
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Memperbarui data produk yang sudah ada
 * - Validasi input dari form edit
 * - Update data produk di database
 * - Menambahkan gambar baru (jika ada)
 * - Menggunakan transaction untuk menjaga konsistensi data
 */
public function update(Request $request, $id)
{
    /**
     * ======================================================
     * VALIDASI INPUT
     * ======================================================
     * - Pastikan kategori valid
     * - Nama wajib diisi
     * - Gambar harus berupa file image (jpg/png)
     */
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'condition' => 'nullable|string',
        'is_maintenance' => 'boolean',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    // Mulai database transaction
    DB::beginTransaction();

    try {
        /**
         * ======================================================
         * AMBIL DATA PRODUK
         * ======================================================
         * - Pastikan produk milik toko seller (security)
         */
        $shop = Auth::user()->shop;
        $product = Product::where('shop_id', $shop->id)->findOrFail($id);

        /**
         * ======================================================
         * GENERATE ULANG KODE PRODUK (JIKA KATEGORI BERUBAH)
         * ======================================================
         * - Jika kategori berubah → generate kode baru
         * - Jika tidak → gunakan kode lama
         */
        $code = $product->code;
        if ($product->category_id != $request->category_id) {
            $code = $this->generateProductCode($request->category_id);
        }

        /**
         * ======================================================
         * UPDATE DATA PRODUK
         * ======================================================
         */
        $product->update([
            'code' => $code,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'condition' => $request->condition,
            'is_maintenance' => $request->boolean('is_maintenance')
        ]);

        /**
         * ======================================================
         * TAMBAH GAMBAR BARU (JIKA ADA)
         * ======================================================
         * - Tidak menghapus gambar lama
         * - Hanya menambahkan gambar baru ke produk
         */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path
                ]);
            }
        }

        // Commit jika semua proses berhasil
        DB::commit();

        return redirect()->route('seller.products.index')
            ->with('success', 'Barang berhasil diperbarui!');

    } catch (\Exception $e) {

        // Rollback jika terjadi error agar data tidak setengah tersimpan
        DB::rollBack();

        return back()->withInput()
            ->with('error', 'Gagal memperbarui barang: ' . $e->getMessage());
    }
}

/**
 * ======================================================
 * MENGHAPUS DATA PRODUK
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Menghapus produk dari database
 * - Menghapus semua gambar terkait dari storage
 * - Menggunakan transaction agar data tetap konsisten
 */
public function destroy($id)
{
    // Mulai database transaction
    DB::beginTransaction();

    try {
        /**
         * ======================================================
         * AMBIL DATA PRODUK
         * ======================================================
         * - Pastikan produk milik toko seller (security)
         */
        $shop = Auth::user()->shop;
        $product = Product::where('shop_id', $shop->id)->findOrFail($id);

        /**
         * ======================================================
         * HAPUS FILE GAMBAR DARI STORAGE
         * ======================================================
         * - Loop semua gambar milik produk
         * - Cek apakah file ada di storage
         * - Hapus file agar tidak menjadi file sampah
         */
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        /**
         * ======================================================
         * HAPUS DATA PRODUK
         * ======================================================
         * - Setelah file dihapus, data produk dihapus dari database
         */
        $product->delete();

        // Commit jika semua proses berhasil
        DB::commit();

        return redirect()->route('seller.products.index')
            ->with('success', 'Barang berhasil dihapus!');

    } catch (\Exception $e) {

        // Rollback jika terjadi error agar data tetap aman
        DB::rollBack();

        return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
    }
}

/**
 * ======================================================
 * MENGHAPUS GAMBAR PRODUK (SATUAN)
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Menghapus satu gambar tertentu dari produk
 * - Menghapus file gambar dari storage
 * - Menghapus data gambar dari database
 * - Menggunakan transaction agar proses aman & konsisten
 */
public function deleteImage($id)
{
    // Mulai database transaction
    DB::beginTransaction();

    try {
        /**
         * ======================================================
         * AMBIL DATA TOKO SELLER
         * ======================================================
         */
        $shop = Auth::user()->shop;

        /**
         * ======================================================
         * AMBIL DATA GAMBAR + VALIDASI KEPEMILIKAN
         * ======================================================
         * - Menggunakan whereHas untuk memastikan:
         *   gambar ini milik produk yang dimiliki oleh toko seller
         * - Ini penting untuk keamanan (tidak bisa hapus gambar toko lain)
         */
        $image = ProductImage::whereHas('product', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id);
        })->findOrFail($id);

        /**
         * ======================================================
         * HAPUS FILE GAMBAR DARI STORAGE
         * ======================================================
         * - Cek apakah file ada
         * - Jika ada → hapus dari storage
         */
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        /**
         * ======================================================
         * HAPUS DATA GAMBAR DI DATABASE
         * ======================================================
         */
        $image->delete();

        // Commit jika berhasil
        DB::commit();

        // Return response JSON (biasanya untuk AJAX)
        return response()->json(['success' => true]);

    } catch (\Exception $e) {

        // Rollback jika terjadi error
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * ======================================================
 * GENERATE KODE PRODUK OTOMATIS
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Membuat kode unik untuk setiap produk
 * - Menggunakan nama kategori sebagai prefix
 * - Menambahkan string random sebagai pembeda
 */
private function generateProductCode($categoryId)
{
    /**
     * ======================================================
     * AMBIL DATA KATEGORI
     * ======================================================
     */
    $category = Category::find($categoryId);

    /**
     * ======================================================
     * JIKA KATEGORI TIDAK DITEMUKAN
     * ======================================================
     * - Gunakan kode default
     * - Format: PRD-XXXXX-random
     */
    if (!$category) {
        return 'PRD-' . strtoupper(Str::random(5)) . '-' . strtolower(substr(md5(time()), 0, 12));
    }

    /**
     * ======================================================
     * BUAT PREFIX DARI NAMA KATEGORI
     * ======================================================
     * - Ambil 5 huruf pertama dari nama kategori
     * - Hilangkan spasi
     * - Ubah menjadi huruf besar
     */
    $prefix = 'PRD-' . strtoupper(substr(str_replace(' ', '', $category->name), 0, 5));

    /**
     * ======================================================
     * BUAT SUFFIX RANDOM
     * ======================================================
     * - Menggunakan kombinasi md5 + waktu + random number
     * - Untuk memastikan kode unik
     */
    $suffix = strtolower(substr(md5(time() . rand()), 0, 12));

    /**
     * ======================================================
     * GABUNGKAN PREFIX DAN SUFFIX
     * ======================================================
     * Contoh hasil:
     * PRD-KAMERA-a1b2c3d4e5f6
     */
    return $prefix . '-' . $suffix;
}

}