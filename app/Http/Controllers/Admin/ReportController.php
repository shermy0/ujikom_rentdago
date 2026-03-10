<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default: 30 hari terakhir
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Query dasar
        $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        // Statistik utama
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pendapatan'  => (clone $baseQuery)->whereIn('status', ['completed', 'returned'])->sum('total_amount'),
            'selesai'     => (clone $baseQuery)->whereIn('status', ['completed', 'returned'])->count(),
            'dibatalkan'  => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'pending'     => (clone $baseQuery)->where('status', 'pending')->count(),
            'berlangsung' => (clone $baseQuery)->where('status', 'ongoing')->count(),
        ];

        // Pesanan per status (untuk chart donut)
        $perStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Pesanan per bulan (12 bulan terakhir, untuk chart bar)
        $perBulan = Order::select(
                DB::raw('YEAR(created_at) as tahun'),
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('count(*) as total'),
                DB::raw('sum(total_amount) as pendapatan')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        // Format data chart bulanan
        $chartLabels = [];
        $chartOrders = [];
        $chartRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $label = $month->locale('id')->isoFormat('MMM YY');
            $chartLabels[] = $label;
            $found = $perBulan->first(fn($r) => $r->tahun == $month->year && $r->bulan == $month->month);
            $chartOrders[]  = $found ? $found->total : 0;
            $chartRevenue[] = $found ? $found->pendapatan : 0;
        }

        // Tabel detail pesanan
        $orders = Order::with(['user', 'productRental.product.shop'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'title'        => 'Laporan Pesanan',
            'breadcrumbs'  => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Laporan', 'url' => '#'],
            ],
            'stats'        => $stats,
            'perStatus'    => $perStatus,
            'chartLabels'  => $chartLabels,
            'chartOrders'  => $chartOrders,
            'chartRevenue' => $chartRevenue,
            'orders'       => $orders,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $baseQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pendapatan'  => (clone $baseQuery)->whereIn('status', ['completed', 'returned'])->sum('total_amount'),
            'selesai'     => (clone $baseQuery)->whereIn('status', ['completed', 'returned'])->count(),
            'dibatalkan'  => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $perStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $orders = Order::with(['user', 'productRental.product.shop'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->get();

        $appName = \App\Models\Setting::first()?->app_name ?? 'RentDago';

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'stats', 'perStatus', 'orders', 'startDate', 'endDate', 'appName'
        ))->setPaper('a4', 'landscape');

        $filename = 'laporan-pesanan-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
