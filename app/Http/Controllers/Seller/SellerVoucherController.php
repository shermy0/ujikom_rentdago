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
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $query = Voucher::where('shop_id', $shop->id)
            ->withCount('usages');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->latest()->paginate(10);

        return view('seller.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        return view('seller.vouchers.create');
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        // Log request untuk debugging
        Log::info('Voucher Store Request', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:vouchers,code',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'max_discount' => 'nullable|numeric|min:1000',
            'min_transaction' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.required' => 'Nama voucher harus diisi',
            'code.unique' => 'Kode voucher sudah digunakan',
            'discount_type.required' => 'Tipe diskon harus dipilih',
            'discount_value.required' => 'Nilai diskon harus diisi',
            'discount_value.min' => 'Nilai diskon minimal 1',
            'min_transaction.required' => 'Minimal transaksi harus diisi',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah tanggal mulai',
        ]);

        // Generate kode otomatis jika tidak diisi
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(8));
        } else {
            $validated['code'] = strtoupper($validated['code']);
        }

        // Validasi tambahan untuk percentage
        if ($validated['discount_type'] === 'percentage') {
            if ($validated['discount_value'] > 100) {
                return back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%']);
            }
        }

        // Convert to integer
        $validated['discount_value'] = (int) $validated['discount_value'];
        $validated['min_transaction'] = (int) $validated['min_transaction'];
        
        if (isset($validated['max_discount'])) {
            $validated['max_discount'] = (int) $validated['max_discount'];
        }

        $validated['shop_id'] = $shop->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            Voucher::create($validated);

            return redirect()
                ->route('seller.vouchers.index')
                ->with('success', 'Voucher berhasil dibuat!');
        } catch (\Exception $e) {
            Log::error('Voucher Create Error', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat voucher: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $voucher = Voucher::where('shop_id', $shop->id)
            ->withCount(['usages', 'users'])
            ->findOrFail($id);

        $recentUsages = $voucher->usages()
            ->with(['user', 'order'])
            ->latest()
            ->limit(10)
            ->get();

        return view('seller.vouchers.show', compact('voucher', 'recentUsages'));
    }

    public function edit($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

        return view('seller.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, $id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:1',
            'max_discount' => 'nullable|numeric|min:1000',
            'min_transaction' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        // Validasi tambahan untuk percentage
        if ($validated['discount_type'] === 'percentage') {
            if ($validated['discount_value'] > 100) {
                return back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%']);
            }
        }

        // Convert to integer
        $validated['discount_value'] = (int) $validated['discount_value'];
        $validated['min_transaction'] = (int) $validated['min_transaction'];
        
        if (isset($validated['max_discount'])) {
            $validated['max_discount'] = (int) $validated['max_discount'];
        }

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $voucher->update($validated);

            return redirect()
                ->route('seller.vouchers.index')
                ->with('success', 'Voucher berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Voucher Update Error', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui voucher: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

        // Cek apakah voucher sudah pernah digunakan
        if ($voucher->usages()->exists()) {
            return back()->with('error', 'Voucher tidak dapat dihapus karena sudah pernah digunakan.');
        }

        $voucher->delete();

        return redirect()
            ->route('seller.vouchers.index')
            ->with('success', 'Voucher berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan'], 404);
        }

        $voucher = Voucher::where('shop_id', $shop->id)->findOrFail($id);

        $voucher->update([
            'is_active' => !$voucher->is_active
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $voucher->is_active,
            'message' => 'Status voucher berhasil diubah'
        ]);
    }
}