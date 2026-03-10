<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\SellerRequest;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index', [
            'totalUsers' => User::count(),
            'totalSellers' => User::where('role', 'seller')->count(),
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),

            'latestSellerRequests' => SellerRequest::latest()->take(5)->get(),
            'latestOrders' => Order::latest()->take(5)->get(),
        ])->with('title', 'Dashboard Admin');
    }
}