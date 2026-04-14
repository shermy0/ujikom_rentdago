<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRental;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProductRentalController extends Controller
{
/**
 * ======================================================
 * MENAMPILKAN LIST PAKET SEWA (PRODUCT RENTAL)
 * ======================================================
 * Fungsi ini digunakan untuk:
 * - Menampilkan semua paket sewa milik seller
 * - Mendukung fitur pencarian (search)
 * - Mendukung filter metode pengiriman (delivery/pickup)
 * - Menambahkan nomor paket per produk
 */
public function index(Request $request)
{
    /**
     * ======================================================
     * VALIDASI TOKO SELLER
     * ======================================================
     * - Pastikan user sudah memiliki toko
     * - Jika belum → redirect ke dashboard
     */
    $shop = Auth::user()->shop;

    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    /**
     * ======================================================
     * QUERY DASAR PRODUCT RENTAL
     * ======================================================
     * - Ambil data paket sewa
     * - Load relasi product, category, dan images (eager loading)
     * - Filter hanya produk milik toko seller
     */
    $query = ProductRental::with([
        'product.category',
        'product.images',
    ])
        ->whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        });

    /**
     * ======================================================
     * FITUR SEARCH (PENCARIAN)
     * ======================================================
     * - Cari berdasarkan nama produk atau kode produk
     * - Menggunakan whereHas karena data ada di tabel product
     */
    if ($request->filled('search')) {
        $search = $request->search;

        $query->whereHas('product', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * ======================================================
     * FILTER BERDASARKAN METODE PENGIRIMAN
     * ======================================================
     * - delivery = delivery / pickup / both
     * - jika 'both' → hanya ambil yang benar-benar both
     * - jika 'pickup' / 'delivery' → ambil yang sesuai + both
     */
    if ($request->filled('delivery')) {
        $delivery = $request->delivery;

        if ($delivery === 'both') {
            $query->where('is_delivery', 'both');
        } else {
            $query->where(function ($q) use ($delivery) {
                $q->where('is_delivery', $delivery)
                  ->orWhere('is_delivery', 'both');
            });
        }
    }

    /**
     * ======================================================
     * EKSEKUSI QUERY
     * ======================================================
     * - Urutkan data terbaru
     * - Gunakan pagination
     */
    $rentals = $query->latest()->paginate(10);

    /**
     * ======================================================
     * MENAMBAHKAN NOMOR PAKET PER PRODUK
     * ======================================================
     * - Setiap produk bisa punya beberapa paket sewa
     * - Nomor paket dihitung per produk (bukan global)
     * Contoh:
     * Produk A → Paket 1, Paket 2
     * Produk B → Paket 1
     */
    $rentalsByProduct = [];

    foreach ($rentals as $rental) {
        $productId = $rental->product_id;

        if (!isset($rentalsByProduct[$productId])) {
            $rentalsByProduct[$productId] = 1;
        } else {
            $rentalsByProduct[$productId]++;
        }

        // Tambahkan atribut tambahan ke object rental
        $rental->package_number = $rentalsByProduct[$productId];
    }

    /**
     * ======================================================
     * RETURN KE VIEW
     * ======================================================
     */
    return view('seller.rentals.index', compact('rentals'))
        ->with('title', 'Paket Sewa');
}

public function create()
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Ambil semua produk milik toko
    // withCount('rentals') → menghitung jumlah paket sewa per produk
    // with('category') → mengambil relasi kategori produk
    // orderBy('name') → urutkan berdasarkan nama produk
    $products = Product::where('shop_id', $shop->id)
        ->withCount('rentals')
        ->with('category')
        ->orderBy('name')
        ->get();

    // Cek apakah toko memiliki kurir aktif
    // Digunakan untuk menentukan apakah opsi delivery bisa digunakan
    $hasCourier = Courier::where('shop_id', $shop->id)
        ->where('status', 'active')
        ->exists();

    // Tampilkan halaman tambah paket sewa
    // Mengirim data produk dan status kurir ke view
    return view('seller.rentals.create', compact('products', 'hasCourier'))
        ->with('title', 'Tambah Paket Sewa');
}

public function store(Request $request)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Validasi input dari form
    // Memastikan semua data sesuai aturan sebelum disimpan ke database
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id', // produk wajib ada dan harus valid di database
        'price' => 'required|integer|min:1000', // harga minimal 1000
        'cycle_value' => 'required|integer|min:1', // durasi sewa minimal 1
        'penalties_price' => 'required|integer|min:1000', // denda minimal 1000
        'penalties_cycle_value' => 'required|integer|min:1', // durasi denda minimal 1
        'is_delivery' => 'required|array|min:1', // harus pilih minimal 1 metode pengiriman
        'is_delivery.*' => 'required|string|in:pickup,delivery', // hanya boleh pickup atau delivery
    ], [
        // Custom pesan error agar lebih user-friendly
        'product_id.required' => 'Produk harus dipilih',
        'product_id.exists' => 'Produk tidak ditemukan',
        'price.required' => 'Harga sewa harus diisi',
        'price.min' => 'Harga sewa minimal Rp 1.000',
        'cycle_value.required' => 'Durasi sewa harus diisi',
        'cycle_value.min' => 'Durasi sewa minimal 1',
        'penalties_price.required' => 'Harga denda harus diisi',
        'penalties_price.min' => 'Harga denda minimal Rp 1.000',
        'penalties_cycle_value.required' => 'Durasi denda harus diisi',
        'penalties_cycle_value.min' => 'Durasi denda minimal 1',
        'is_delivery.required' => 'Pilih minimal satu metode pengiriman',
        'is_delivery.min' => 'Pilih minimal satu metode pengiriman',
        'is_delivery.*.required' => 'Metode pengiriman tidak boleh kosong',
        'is_delivery.*.in' => 'Metode pengiriman harus Ambil Sendiri atau Antar',
    ]);

    // Verifikasi bahwa produk yang dipilih benar-benar milik toko user
    // Mencegah user memilih produk dari toko lain (keamanan)
    $product = Product::where('id', $validated['product_id'])
        ->where('shop_id', $shop->id)
        ->firstOrFail();

    // Ambil pilihan metode pengiriman dari input (array)
    $deliveryTypes = $validated['is_delivery'];

    // ===== CEK KURIR JIKA PILIH DELIVERY (SEBELUM SIMPAN) =====
    // Jika user memilih metode antar (delivery), maka harus ada kurir aktif
    if (in_array('delivery', $deliveryTypes)) {
        $hasActiveCourier = Courier::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->exists();

        // Jika tidak ada kurir aktif, batalkan proses dan kirim error
        if (!$hasActiveCourier) {
            return back()
                ->withErrors([
                    'is_delivery' => 'Tidak bisa memilih metode antar karena toko belum memiliki kurir aktif.'
                ])
                ->withInput();
        }
    }

    // Konversi array metode pengiriman menjadi format string (enum di database)
    // Jika pilih dua-duanya → pickup_delivery
    if (count($deliveryTypes) == 2) {
        $validated['is_delivery'] = 'pickup_delivery';
    } else {
        // Jika hanya satu, ambil nilai pertama (pickup atau delivery)
        $validated['is_delivery'] = $deliveryTypes[0];
    }

    // Simpan data paket rental ke database
    ProductRental::create($validated);

    // Redirect ke halaman daftar rental dengan pesan sukses
    return redirect()
        ->route('seller.rentals.index')
        ->with('success', 'Paket rental berhasil ditambahkan!');
}

public function edit($id)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Ambil data paket rental berdasarkan ID
    // with('product.category', 'product.images') → eager loading relasi produk, kategori, dan gambar
    // whereHas → memastikan paket rental tersebut berasal dari produk milik toko user (keamanan)
    $rental = ProductRental::with('product.category', 'product.images')
        ->whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })
        ->findOrFail($id); // jika tidak ditemukan, otomatis 404

    // Cek apakah toko memiliki kurir aktif
    // Digunakan untuk menentukan apakah opsi delivery bisa digunakan saat edit
    $hasCourier = Courier::where('shop_id', $shop->id)
        ->where('status', 'active')
        ->exists();

    // Tampilkan halaman edit paket sewa
    // Mengirim data rental dan status kurir ke view
    return view('seller.rentals.edit', compact('rental', 'hasCourier'))
        ->with('title', 'Edit Paket Sewa');
}

public function update(Request $request, $id)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Ambil data rental berdasarkan ID
    // whereHas → memastikan rental ini milik produk dari toko user (keamanan)
    $rental = ProductRental::whereHas('product', function ($q) use ($shop) {
        $q->where('shop_id', $shop->id);
    })->findOrFail($id);

    // Validasi input dari form edit
    $validated = $request->validate([
        'price' => 'required|integer|min:1000', // harga minimal 1000
        'cycle_value' => 'required|integer|min:1', // durasi minimal 1
        'penalties_price' => 'required|integer|min:1000', // denda minimal 1000
        'penalties_cycle_value' => 'required|integer|min:1', // durasi denda minimal 1
        'is_delivery' => 'required|array|min:1', // minimal pilih satu metode
        'is_delivery.*' => 'required|string|in:pickup,delivery', // hanya pickup/delivery
    ], [
        // Custom pesan error
        'price.required' => 'Harga sewa harus diisi',
        'price.min' => 'Harga sewa minimal Rp 1.000',
        'cycle_value.required' => 'Durasi sewa harus diisi',
        'cycle_value.min' => 'Durasi sewa minimal 1',
        'penalties_price.required' => 'Harga denda harus diisi',
        'penalties_price.min' => 'Harga denda minimal Rp 1.000',
        'penalties_cycle_value.required' => 'Durasi denda harus diisi',
        'penalties_cycle_value.min' => 'Durasi denda minimal 1',
        'is_delivery.required' => 'Pilih minimal satu metode pengiriman',
        'is_delivery.min' => 'Pilih minimal satu metode pengiriman',
        'is_delivery.*.required' => 'Metode pengiriman tidak boleh kosong',
        'is_delivery.*.in' => 'Metode pengiriman harus Ambil Sendiri atau Antar',
    ]);

    // Konversi array is_delivery (dari checkbox) ke format string (enum di database)
    $deliveryTypes = $validated['is_delivery'];

    // Jika pilih dua metode → gabungkan jadi pickup_delivery
    if (count($deliveryTypes) == 2) {
        $validated['is_delivery'] = 'pickup_delivery';
    } else {
        // Jika hanya satu → ambil nilai pertama
        $validated['is_delivery'] = $deliveryTypes[0];
    }

    // Update data rental di database
    $rental->update($validated);

    // Redirect ke halaman list rental dengan pesan sukses
    return redirect()
        ->route('seller.rentals.index')
        ->with('success', 'Paket rental berhasil diperbarui!');
}


public function show($id)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Ambil detail rental berdasarkan ID
    // with → eager loading relasi product, category, dan images
    // whereHas → memastikan data hanya milik toko user (keamanan)
    $rental = ProductRental::with(['product.category', 'product.images'])
        ->whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })
        ->findOrFail($id);

    // Tampilkan halaman detail paket sewa
    return view('seller.rentals.show', compact('rental'))
        ->with('title', 'Detail Paket Sewa');
}
public function destroy($id)
{
    // Ambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Ambil data rental berdasarkan ID
    // whereHas → memastikan bahwa paket rental ini berasal dari produk milik toko user
    // (mencegah user menghapus data milik toko lain / keamanan)
    $rental = ProductRental::whereHas('product', function ($q) use ($shop) {
        $q->where('shop_id', $shop->id);
    })->findOrFail($id);

    // Hapus data paket rental dari database
    $rental->delete();

    // Redirect ke halaman daftar rental dengan pesan sukses
    return redirect()
        ->route('seller.rentals.index')
        ->with('success', 'Paket rental berhasil dihapus!');
}

}
