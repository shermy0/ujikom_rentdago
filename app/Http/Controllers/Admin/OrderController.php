<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'productRental.product.shop', 'payment']);

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan payment status
        if ($request->filled('payment_status')) {
            $query->whereHas('payment', fn($q) => $q->where('payment_status', $request->payment_status));
        }

        // Search berdasarkan order code atau nama user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistik
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'ongoing' => Order::where('status', 'ongoing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'paid'   => \App\Models\Payment::where('payment_status', 'paid')->count(),
            'unpaid' => \App\Models\Payment::where('payment_status', 'unpaid')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'))->with('title', 'Data Pemesanan');
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'productRental.product.shop',
            'productRental.product.images',
            'payment',
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }
}
