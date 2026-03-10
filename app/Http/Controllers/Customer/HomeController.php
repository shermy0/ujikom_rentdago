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
        $categories = Category::orderBy('name')->get();

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
        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
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
            'tab'
        ))->with('title', 'Beranda');
    }

    public function show(Product $product)
    {
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