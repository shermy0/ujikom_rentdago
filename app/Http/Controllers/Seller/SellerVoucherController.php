<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SellerVoucherController extends Controller
{


public function index(Request $request)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Membuat query awal untuk mengambil voucher berdasarkan shop_id
    // withCount('usages') digunakan untuk menghitung berapa kali voucher telah digunakan
    $query = Voucher::where('shop_id', $shop->id)
        ->withCount('usages');

    // =========================
    // FILTER BERDASARKAN STATUS
    // =========================
    // Jika ada parameter status pada request
    if ($request->filled('status')) {
        // Jika status = active, ambil voucher yang aktif
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } 
        // Jika status = inactive, ambil voucher yang tidak aktif
        elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
    }

    // =========================
    // FITUR SEARCH (PENCARIAN)
    // =========================
    // Jika ada input search dari user
    if ($request->filled('search')) {
        $search = $request->search;

        // Mencari voucher berdasarkan kode atau nama (menggunakan LIKE untuk pencarian fleksibel)
        $query->where(function($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }

    // Mengambil data voucher terbaru dan membaginya per halaman (pagination 10 data)
    $vouchers = $query->latest()->paginate(10);

    // Mengirim data voucher ke view beserta judul halaman
    return view('seller.vouchers.index', compact('vouchers'))->with('title', 'Kelola Voucher');
}

public function create()
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard dengan pesan error
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Menampilkan halaman form untuk membuat voucher baru
    return view('seller.vouchers.create')->with('title', 'Buat Voucher');
}

public function store(Request $request)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Logging seluruh request untuk kebutuhan debugging (melihat data yang dikirim dari form)
    Log::info('Voucher Store Request', $request->all());

    // =========================
    // VALIDASI INPUT
    // =========================
    $validated = $request->validate([
        'name' => 'required|string|max:255', // Nama voucher wajib diisi
        'code' => 'nullable|string|max:50|unique:vouchers,code', // Kode boleh kosong tapi harus unik jika diisi
        'description' => 'nullable|string|max:500', // Deskripsi opsional
        'discount_type' => 'required|in:percentage,fixed', // Tipe diskon harus percentage atau fixed
        'discount_value' => 'required|numeric|min:1', // Nilai diskon minimal 1
        'max_discount' => 'nullable|numeric|min:1000', // Maksimal diskon opsional
        'min_transaction' => 'required|numeric|min:0', // Minimal transaksi
        'valid_from' => 'nullable|date', // Tanggal mulai opsional
        'valid_until' => 'nullable|date|after_or_equal:valid_from', // Tanggal akhir tidak boleh sebelum tanggal mulai
    ], [
        // Custom error message agar lebih user-friendly
        'name.required' => 'Nama voucher harus diisi',
        'code.unique' => 'Kode voucher sudah digunakan',
        'discount_type.required' => 'Tipe diskon harus dipilih',
        'discount_value.required' => 'Nilai diskon harus diisi',
        'discount_value.min' => 'Nilai diskon minimal 1',
        'min_transaction.required' => 'Minimal transaksi harus diisi',
        'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah tanggal mulai',
    ]);

    // =========================
    // GENERATE KODE VOUCHER
    // =========================
    // Jika user tidak mengisi kode, sistem akan generate otomatis
    if (empty($validated['code'])) {
        $validated['code'] = strtoupper(Str::random(8)); // Random 8 karakter uppercase
    } else {
        $validated['code'] = strtoupper($validated['code']); // Pastikan kode selalu uppercase
    }

    // =========================
    // VALIDASI TAMBAHAN
    // =========================
    // Jika tipe diskon adalah percentage, pastikan tidak lebih dari 100%
    if ($validated['discount_type'] === 'percentage') {
        if ($validated['discount_value'] > 100) {
            return back()
                ->withInput()
                ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%']);
        }
    }

    // =========================
    // KONVERSI TIPE DATA
    // =========================
    // Mengubah nilai menjadi integer agar konsisten di database
    $validated['discount_value'] = (int) $validated['discount_value'];
    $validated['min_transaction'] = (int) $validated['min_transaction'];
    
    if (isset($validated['max_discount'])) {
        $validated['max_discount'] = (int) $validated['max_discount'];
    }

    // Menambahkan shop_id agar voucher terhubung dengan toko
    $validated['shop_id'] = $shop->id;

    // Menentukan status aktif berdasarkan checkbox (true jika dicentang)
    $validated['is_active'] = $request->has('is_active') ? true : false;

    try {
        // Menyimpan data voucher ke database
        Voucher::create($validated);

        // Redirect ke halaman list voucher dengan pesan sukses
        return redirect()
            ->route('seller.vouchers.index')
            ->with('success', 'Voucher berhasil dibuat!');
    } catch (\Exception $e) {
        // Logging error jika terjadi kegagalan saat menyimpan
        Log::error('Voucher Create Error', [
            'error' => $e->getMessage(),
            'data' => $validated
        ]);

        // Kembali ke halaman form dengan pesan error dan input sebelumnya
        return back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat membuat voucher: ' . $e->getMessage());
    }
}

public function show($id)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Mengambil data voucher berdasarkan shop_id (biar tidak bisa akses voucher toko lain)
    // withCount digunakan untuk menghitung:
    // - usages → berapa kali voucher digunakan
    // - users → berapa user yang pernah menggunakan voucher
    $voucher = Voucher::where('shop_id', $shop->id)
        ->withCount(['usages', 'users'])
        ->findOrFail($id);

    // Mengambil 10 penggunaan voucher terbaru
    // Sekalian ambil relasi user dan order untuk ditampilkan
    $recentUsages = $voucher->usages()
        ->with(['user', 'order'])
        ->latest()
        ->limit(10)
        ->get();

    // Mengirim data voucher dan riwayat penggunaan ke halaman detail
    return view('seller.vouchers.show', compact('voucher', 'recentUsages'))->with('title', 'Detail Voucher');
}

public function edit($id)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Mengambil data voucher berdasarkan shop_id
    // untuk memastikan hanya voucher milik toko tersebut yang bisa diedit
    $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

    // Menampilkan halaman edit voucher dengan data voucher
    return view('seller.vouchers.edit', compact('voucher'))->with('title', 'Edit Voucher');
}

public function update(Request $request, $id)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Mengambil data voucher berdasarkan shop_id
    // untuk memastikan hanya voucher milik toko tersebut yang bisa diupdate
    $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

    // =========================
    // VALIDASI INPUT
    // =========================
    $validated = $request->validate([
        'name' => 'required|string|max:255', // Nama voucher wajib diisi
        // Unique tapi ignore ID voucher saat ini (agar tidak bentrok dengan dirinya sendiri)
        'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
        'description' => 'nullable|string|max:500', // Deskripsi opsional
        'discount_type' => 'required|in:percentage,fixed', // Tipe diskon
        'discount_value' => 'required|numeric|min:1', // Nilai diskon minimal 1
        'max_discount' => 'nullable|numeric|min:1000', // Maksimal diskon opsional
        'min_transaction' => 'required|numeric|min:0', // Minimal transaksi
        'valid_from' => 'nullable|date', // Tanggal mulai
        'valid_until' => 'nullable|date|after_or_equal:valid_from', // Tanggal akhir ≥ tanggal mulai
    ]);

    // =========================
    // VALIDASI TAMBAHAN
    // =========================
    // Jika tipe diskon percentage, tidak boleh lebih dari 100%
    if ($validated['discount_type'] === 'percentage') {
        if ($validated['discount_value'] > 100) {
            return back()
                ->withInput()
                ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%']);
        }
    }

    // =========================
    // KONVERSI TIPE DATA
    // =========================
    // Mengubah nilai numerik menjadi integer untuk konsistensi database
    $validated['discount_value'] = (int) $validated['discount_value'];
    $validated['min_transaction'] = (int) $validated['min_transaction'];
    
    if (isset($validated['max_discount'])) {
        $validated['max_discount'] = (int) $validated['max_discount'];
    }

    // Mengubah kode voucher menjadi huruf besar semua
    $validated['code'] = strtoupper($validated['code']);

    // Menentukan status aktif berdasarkan checkbox (true jika dicentang)
    $validated['is_active'] = $request->has('is_active') ? true : false;

    try {
        // Mengupdate data voucher di database
        $voucher->update($validated);

        // Redirect ke halaman list voucher dengan pesan sukses
        return redirect()
            ->route('seller.vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui!');
    } catch (\Exception $e) {
        // Logging error untuk debugging jika terjadi kegagalan
        Log::error('Voucher Update Error', [
            'error' => $e->getMessage(),
            'data' => $validated
        ]);

        // Kembali ke halaman sebelumnya dengan pesan error
        return back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat memperbarui voucher: ' . $e->getMessage());
    }
}

public function destroy($id)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika user belum memiliki toko, redirect ke dashboard
    if (!$shop) {
        return redirect()
            ->route('seller.dashboard.index')
            ->with('error', 'Buka toko terlebih dahulu.');
    }

    // Mengambil data voucher berdasarkan shop_id
    // untuk memastikan hanya voucher milik toko tersebut yang bisa dihapus
    $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

    // =========================
    // VALIDASI PENGHAPUSAN
    // =========================
    // Mengecek apakah voucher sudah pernah digunakan
    // Jika sudah pernah dipakai, maka tidak boleh dihapus
    if ($voucher->usages()->exists()) {
        return back()->with('error', 'Voucher tidak dapat dihapus karena sudah pernah digunakan.');
    }

    // Menghapus data voucher dari database
    $voucher->delete();

        return redirect()
            ->route('seller.vouchers.index')
            ->with('success', 'Voucher berhasil dihapus!');
    }

public function toggleStatus($id)
{
    // Mengambil data toko milik user yang sedang login
    $shop = Auth::user()->shop;

    // Jika toko tidak ditemukan, kembalikan response JSON error
    if (!$shop) {
        return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan'], 404);
    }

    // Mengambil data voucher berdasarkan shop_id
    // untuk memastikan hanya voucher milik toko tersebut yang bisa diubah statusnya
    $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

    // =========================
    // TOGGLE STATUS
    // =========================
    // Mengubah nilai is_active:
    // - jika sebelumnya true → jadi false
    // - jika sebelumnya false → jadi true
    $voucher->update([
        'is_active' => !$voucher->is_active
    ]);

    // Mengembalikan response JSON berisi status terbaru
    return response()->json([
        'success' => true,
        'is_active' => $voucher->is_active, // status terbaru setelah diubah
        'message' => 'Status voucher berhasil diubah'
    ]);
}

}