<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProductRentalController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $query = ProductRental::with([
            'product.category',
            'product.images',
        ])
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by delivery type
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

        $rentals = $query->latest()->paginate(10);

        // Tambahkan nomor paket untuk setiap produk
        $rentalsByProduct = [];
        foreach ($rentals as $rental) {
            $productId = $rental->product_id;
            if (!isset($rentalsByProduct[$productId])) {
                $rentalsByProduct[$productId] = 1;
            } else {
                $rentalsByProduct[$productId]++;
            }
            $rental->package_number = $rentalsByProduct[$productId];
        }

        return view('seller.rentals.index', compact('rentals'))->with('title', 'Paket Sewa');;
    }

    public function create()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        // Ambil semua produk dengan jumlah rental yang sudah ada
        $products = Product::where('shop_id', $shop->id)
            ->withCount('rentals')
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('seller.rentals.create', compact('products'))->with('title', 'Tambah Paket Sewa');;
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|integer|min:1000',
            'cycle_value' => 'required|integer|min:1',
            'penalties_price' => 'required|integer|min:1000',
            'penalties_cycle_value' => 'required|integer|min:1',
            'is_delivery' => 'required|array|min:1',
            'is_delivery.*' => 'required|string|in:pickup,delivery',
        ], [
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

        // Verify product belongs to shop
        $product = Product::where('id', $validated['product_id'])
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        ProductRental::create($validated);

        return redirect()
            ->route('seller.rentals.index')
            ->with('success', 'Paket rental berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $rental = ProductRental::with('product.category', 'product.images')
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        return view('seller.rentals.edit', compact('rental'))->with('title', 'Edit Paket Sewa');;
    }

    public function update(Request $request, $id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $rental = ProductRental::whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'price' => 'required|integer|min:1000',
            'cycle_value' => 'required|integer|min:1',
            'penalties_price' => 'required|integer|min:1000',
            'penalties_cycle_value' => 'required|integer|min:1',
            'is_delivery' => 'required|array|min:1',
            'is_delivery.*' => 'required|string|in:pickup,delivery',
        ], [
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

        $rental->update($validated);

        return redirect()
            ->route('seller.rentals.index')
            ->with('success', 'Paket rental berhasil diperbarui!');
    }

    public function show($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $rental = ProductRental::with(['product.category', 'product.images'])
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })
            ->findOrFail($id);

        return view('seller.rentals.show', compact('rental'))->with('title', 'Detail Paket Sewa');;
    }

    public function destroy($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $rental = ProductRental::whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })->findOrFail($id);

        $rental->delete();

        return redirect()
            ->route('seller.rentals.index')
            ->with('success', 'Paket rental berhasil dihapus!');
    }
}
