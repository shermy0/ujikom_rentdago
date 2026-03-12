<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRental;
use App\Models\Category;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // =========================
        // QUERY PARAMS
        // =========================
        $search = $request->query('search');
        $categorySlug = $request->query('category');
        $tab = $request->query('tab', 'all'); // all | latest | popular

        // =========================
        // CATEGORIES
        // =========================
        // Hanya tampilkan kategori parent (root) saja, dengan children untuk badge
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        // =========================
        // BASE QUERY PRODUK
        // =========================
        $query = Product::with([
            'images',
            'category',
            'rentals' => function ($q) {
                $q->orderBy('price', 'asc');
            }
        ])
        ->withCount([
            'orders as rent_count' => function ($q) {
                $q->whereIn('status', ['paid', 'ongoing', 'completed']);
            },
            'orders as renter_count' => function ($q) {
                $q->select(\DB::raw('count(distinct user_id)'))
                  ->whereIn('status', ['paid', 'ongoing', 'completed']);
            }
        ])
        ->where('is_maintenance', 0);

        // =========================
        // TAB FILTER
        // =========================
        if ($tab === 'latest') {
            $query->latest();
        }

        if ($tab === 'popular') {
            $query
                ->having('rent_count', '>=', 1)
                ->orderByDesc('rent_count');
        }

        // =========================
        // SEARCH
        // =========================
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // =========================
        // CATEGORY FILTER
        // =========================
        $selectedCategoryObj = null;
        $subCategories = collect(); // Untuk menampung sub-kategori yang akan ditampilkan
        $activeParentSlug = null; // Menyimpan slug parent yang sedang aktif

        if ($categorySlug) {
            // Cari kategori berdasarkan slug
            $selectedCategoryObj = Category::with('parent', 'children')->where('slug', $categorySlug)->first();

            if ($selectedCategoryObj) {
                // Tentukan sub-kategori yang akan ditampilkan di bawah
                if (is_null($selectedCategoryObj->parent_id)) {
                    // Ini parent: tampilkan anak-anaknya, slug parent adalah miliknya sendiri
                    $subCategories = $selectedCategoryObj->children;
                    $activeParentSlug = $selectedCategoryObj->slug;

                    // Filter produk: ambil dari parent + semua anak-anaknya
                    $categoryIds = collect([$selectedCategoryObj->id])
                        ->merge($selectedCategoryObj->children()->pluck('id'));
                } else {
                    // Ini child: tampilkan saudara-saudaranya (anak dari parent-nya)
                    $subCategories = $selectedCategoryObj->parent ? $selectedCategoryObj->parent->children : collect();
                    $activeParentSlug = $selectedCategoryObj->parent ? $selectedCategoryObj->parent->slug : null;

                    // Filter produk: cukup dari child itu sendiri
                    $categoryIds = collect([$selectedCategoryObj->id]);
                }

                $query->whereHas('category', function ($q) use ($categoryIds) {
                    $q->whereIn('id', $categoryIds);
                });
            }
        }

        // =========================
        // PAGINATION
        // =========================
        $products = $query
            ->paginate(8)
            ->withQueryString();

        // =========================
        // RETURN VIEW
        // =========================
        return view('home.index', compact(
            'products',
            'categories',
            'search',
            'categorySlug',
            'tab',
            'subCategories',
            'activeParentSlug'
        ))->with('title', 'Beranda');
    }

public function show(string $slug, Product $product)
{
    // validasi biar gak asal
    if ($product->shop->slug !== $slug) {
        abort(404);
    }

    $product->load([
        'images',
        'category',
        'rentals',
        'shop',
    ]);

    return view('home.product-detail', compact('product'))
        ->with('title', 'Detail Produk');
}

    public function checkout(Product $product)
    {
        // Load relasi yang dibutuhkan
        $product->load([
            'images',
            'category',
            'rentals',
            'shop',
            'rentals.orders' => function ($q) {
                $q->whereIn('status', ['confirmed', 'ongoing']);
            }
        ]);

        // Ambil alamat user
        $addresses = auth()->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->get();

        return view('home.checkout', compact('product', 'addresses'))
            ->with('title', 'Checkout - ' . $product->name);
    }


    
}