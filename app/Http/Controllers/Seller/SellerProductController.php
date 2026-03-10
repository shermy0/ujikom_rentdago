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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SellerProductController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu sebelum mengelola produk.');
        }

        $query = Product::with(['category', 'images'])
            ->where('shop_id', $shop->id);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('is_maintenance', 0);
            } elseif ($request->status === 'maintenance') {
                $query->where('is_maintenance', 1);
            }
        }

        $products   = $query->latest()->paginate(10);
        $categories = Category::orderBy('name')->get();

        return view('seller.products.index', compact('products', 'categories'))->with('title', 'Daftar Produk');
    }

    public function show($id)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()
                ->route('seller.dashboard.index')
                ->with('error', 'Buka toko terlebih dahulu.');
        }

        $product = Product::with(['category', 'images'])
            ->where('shop_id', $shop->id)
            ->findOrFail($id);

        return view('seller.products.show', compact('product'))->with('title', 'Detail Produk');;
    }

    // Method baru untuk download QR Code
    public function downloadQrCode($id)
    {
        $shop = Auth::user()->shop;
        $product = Product::where('shop_id', $shop->id)->findOrFail($id);

        // Generate QR Code dengan HANYA kode produk
        $qrCode = QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($product->code);

        $filename = 'qrcode-' . $product->code . '.png';

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // Method untuk generate QR Code inline (untuk ditampilkan di view)
    public function generateQrCode($id)
    {
        $shop = Auth::user()->shop;
        $product = Product::where('shop_id', $shop->id)->findOrFail($id);

        // Generate QR Code dengan HANYA kode produk
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($product->code);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('seller.products.create', compact('categories'))->with('title', 'Tambah Produk');;
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|string',
            'is_maintenance' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $code = $this->generateProductCode($request->category_id);
            $shop = Auth::user()->shop;

            $product = Product::create([
                'code' => $code,
                'category_id' => $request->category_id,
                'shop_id' => $shop->id,
                'name' => $request->name,
                'description' => $request->description,
                'condition' => $request->condition,
                'is_maintenance' => $request->boolean('is_maintenance')
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('seller.products.index')
                ->with('success', 'Barang berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

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

    public function edit($id)
    {
        $shop = Auth::user()->shop;

        $product = Product::with('images')
            ->where('shop_id', $shop->id)
            ->findOrFail($id);

        $categories = Category::orderBy('name')->get();

        return view('seller.products.edit', compact('product', 'categories'))->with('title', 'Edit Produk');;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|string',
            'is_maintenance' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $shop = Auth::user()->shop;
            $product = Product::where('shop_id', $shop->id)->findOrFail($id);

            $code = $product->code;
            if ($product->category_id != $request->category_id) {
                $code = $this->generateProductCode($request->category_id);
            }

            $product->update([
                'code' => $code,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'condition' => $request->condition,
                'is_maintenance' => $request->boolean('is_maintenance')
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('seller.products.index')
                ->with('success', 'Barang berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui barang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $shop = Auth::user()->shop;
            $product = Product::where('shop_id', $shop->id)->findOrFail($id);

            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }

            $product->delete();

            DB::commit();
            return redirect()->route('seller.products.index')
                ->with('success', 'Barang berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    public function deleteImage($id)
    {
        DB::beginTransaction();
        try {
            $shop = Auth::user()->shop;

            $image = ProductImage::whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })->findOrFail($id);

            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateProductCode($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return 'PRD-' . strtoupper(Str::random(5)) . '-' . strtolower(substr(md5(time()), 0, 12));
        }

        $prefix = 'PRD-' . strtoupper(substr(str_replace(' ', '', $category->name), 0, 5));
        $suffix = strtolower(substr(md5(time() . rand()), 0, 12));

        return $prefix . '-' . $suffix;
    }
}