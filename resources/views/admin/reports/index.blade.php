@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .stat-icon.orange { background: rgba(238,77,45,0.1); color: #ee4d2d; }
    .stat-icon.green  { background: rgba(34,197,94,0.1);  color: #16a34a; }
    .stat-icon.blue   { background: rgba(59,130,246,0.1);  color: #3b82f6; }
    .stat-icon.red    { background: rgba(239,68,68,0.1);   color: #ef4444; }
    .stat-value { font-size: 24px; font-weight: 700; color: #1f2937; }
    .stat-label { font-size: 13px; color: #6b7280; margin-top: 2px; }
    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        height: 100%;
    }
    .chart-title { font-size: 15px; font-weight: 600; color: #374151; margin-bottom: 16px; }
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .table-card .table { margin-bottom: 0; }
    .table-card .table thead th {
        background: #f9fafb;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .table-card .table tbody td { padding: 12px 16px; vertical-align: middle; font-size: 13px; white-space: nowrap; }
    .badge-status { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 500; }

    .report-header {
        gap: 12px;
    }

    .table-card .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x;
    }

    .table-card .table {
        min-width: 900px;
    }

    @media (max-width: 991.98px) {
        .report-header {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .report-header .btn {
            width: 100%;
            justify-content: center;
        }

        .filter-bar {
            padding: 14px;
        }

        .stat-card {
            padding: 14px;
            gap: 10px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }

        .stat-value {
            font-size: 18px;
        }

        .chart-card {
            padding: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 report-header">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1f2937;">Laporan Pesanan</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Analitik & rekap data penyewaan barang</p>
    </div>
    <a href="{{ route('admin.reports.pdf', request()->query()) }}" 
       class="btn btn-danger d-flex align-items-center gap-2" 
       style="background:#ee4d2d; border:none; border-radius:8px; font-size:14px;">
        <i class="bi bi-file-earmark-pdf"></i> Export PDF
    </a>
</div>

{{-- Filter Bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Tanggal Akhir</label>
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn flex-fill" style="background:#ee4d2d; color:white; border-radius:8px;">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                Reset
            </a>
        </div>
    </form>
    <div class="mt-2" style="font-size:12px; color:#9ca3af;">
        Menampilkan data: <strong>{{ $startDate->format('d M Y') }}</strong> – <strong>{{ $endDate->format('d M Y') }}</strong>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">Total Pesanan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-value" style="font-size:18px;">Rp {{ number_format($stats['pendapatan'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['selesai']) }}</div>
                <div class="stat-label">Pesanan Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['dibatalkan']) }}</div>
                <div class="stat-label">Pesanan Dibatalkan</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="chart-card">
            <div class="chart-title"><i class="bi bi-bar-chart me-2" style="color:#ee4d2d;"></i>Pesanan & Pendapatan per Bulan</div>
            <canvas id="chartBulanan" height="100"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <div class="chart-title"><i class="bi bi-pie-chart me-2" style="color:#ee4d2d;"></i>Pesanan per Status</div>
            <canvas id="chartStatus" height="200"></canvas>
            <div id="statusLegend" class="mt-3" style="font-size:12px;"></div>
        </div>
    </div>
</div>

{{-- Tabel Pesanan --}}
<div class="table-card mb-4">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <span class="fw-semibold" style="font-size:14px; color:#374151;">
            <i class="bi bi-table me-2" style="color:#ee4d2d;"></i>Detail Pesanan
        </span>
        <span class="text-muted" style="font-size:12px;">{{ $orders->total() }} pesanan ditemukan</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode Pesanan</th>
                    <th>Customer</th>
                    <th>Produk</th>
                    <th>Toko</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $i => $order)
                <tr>
                    <td class="text-muted">{{ $orders->firstItem() + $i }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none fw-semibold" style="color:#ee4d2d;">
                            {{ $order->order_code }}
                        </a>
                    </td>
                    <td>{{ $order->user->name ?? '-' }}</td>
                    <td>{{ $order->productRental->product->name ?? '-' }}</td>
                    <td>{{ $order->productRental->product->shop->name_store ?? '-' }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>
                        @php
                            $colorMap = [
                                'pending'   => 'warning',
                                'paid'      => 'info',
                                'confirmed' => 'primary',
                                'ongoing'   => 'success',
                                'completed' => 'success',
                                'returned'  => 'info',
                                'cancelled' => 'danger',
                                'penalty'   => 'danger',
                            ];
                            $c = $colorMap[$order->status] ?? 'secondary';
                        @endphp
                        <span class="badge badge-status bg-{{ $c }}">{{ \App\Models\Order::getStatusLabel($order->status) }}</span>
                    </td>
                    <td class="text-end fw-semibold">Rp {{ number_format($order->payment->total_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Tidak ada data pesanan pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="d-flex justify-content-center py-3">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Chart Bar - Pesanan per Bulan
const ctxBar = document.getElementById('chartBulanan').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Jumlah Pesanan',
                data: @json($chartOrders),
                backgroundColor: 'rgba(238,77,45,0.7)',
                borderRadius: 6,
                borderSkipped: false,
                yAxisID: 'yOrders',
            },
            {
                label: 'Pendapatan (Rp)',
                data: @json($chartRevenue),
                type: 'line',
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                yAxisID: 'yRevenue',
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true, position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } } },
        scales: {
            yOrders: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                ticks: { precision: 0 }
            },
            yRevenue: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                grid: { drawOnChartArea: false },
                ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }
            },
            x: { grid: { display: false } }
        }
    }
});

// Chart Donut - Status
const statusData = @json($perStatus);
const statusLabels = {
    pending:   'Menunggu',
    paid:      'Dibayar',
    confirmed: 'Dikonfirmasi',
    ongoing:   'Berlangsung',
    completed: 'Selesai',
    returned:  'Dikembalikan',
    cancelled: 'Dibatalkan',
    penalty:   'Denda',
};
const colors = ['#ee4d2d','#3b82f6','#f59e0b','#22c55e','#16a34a','#06b6d4','#ef4444','#8b5cf6'];
const labels = Object.keys(statusData).map(k => statusLabels[k] || k);
const values = Object.values(statusData);

const ctxDoughnut = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxDoughnut, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{ data: values, backgroundColor: colors.slice(0, values.length), borderWidth: 2 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } },
        cutout: '65%',
    }
});
</script>
@endpush
