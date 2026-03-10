<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

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

        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', [
            'title' => 'Data Barang',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Barang', 'url' => '#']
            ],
            'products' => $products,
            'categories' => $categories
        ])->with('title', 'Data Barang');
    }



    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return view('admin.products.show', [
            'title' => 'Detail Barang',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Data Barang', 'url' => route('admin.products.index')],
                ['title' => 'Detail', 'url' => '#']
            ],
            'product' => $product
        ]);
    }



    private function generateProductCode($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return 'PRD-' . strtoupper(Str::random(5)) . '-' . strtolower(substr(md5(time()), 0, 12));
        }

        // Get category prefix (first 5 chars uppercase)
        $prefix = 'PRD-' . strtoupper(substr(str_replace(' ', '', $category->name), 0, 5));

        // Generate unique suffix
        $suffix = strtolower(substr(md5(time() . rand()), 0, 12));

        return $prefix . '-' . $suffix;
    }
}
