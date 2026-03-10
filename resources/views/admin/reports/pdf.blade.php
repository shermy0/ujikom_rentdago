<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pesanan - {{ $appName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; background: white; }

        /* Header */
        .header { background: #ee4d2d; color: white; padding: 16px 24px; margin-bottom: 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: flex-start; }
        .app-name { font-size: 20px; font-weight: bold; }
        .doc-title { font-size: 14px; margin-top: 4px; opacity: 0.9; }
        .period { font-size: 11px; margin-top: 2px; opacity: 0.8; }
        .print-info { text-align: right; font-size: 10px; opacity: 0.85; }

        /* Stats */
        .stats-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 16px; }
        .stats-row { display: table-row; }
        .stat-box {
            display: table-cell;
            width: 25%;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            text-align: center;
        }
        .stat-val { font-size: 20px; font-weight: bold; color: #ee4d2d; }
        .stat-lbl { font-size: 10px; color: #6b7280; margin-top: 2px; }

        /* Section Title */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            border-left: 3px solid #ee4d2d;
            padding-left: 8px;
            margin: 16px 0 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Table */
        .status-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .status-table th { background: #f3f4f6; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .status-table td { padding: 7px 12px; border-bottom: 1px solid #f3f4f6; }
        .status-table tr:last-child td { border-bottom: none; }

        /* Main Table */
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th {
            background: #ee4d2d;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .orders-table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        .orders-table tr:nth-child(even) td { background: #fafafa; }
        .orders-table tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }

        /* Badge */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info    { background: #dbeafe; color: #1e40af; }
        .badge-purple  { background: #ede9fe; color: #5b21b6; }
        .badge-secondary { background: #f3f4f6; color: #374151; }

        /* Footer */
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-flex">
        <div>
            <div class="app-name">{{ $appName }}</div>
            <div class="doc-title">Laporan Analitik Pesanan</div>
            <div class="period">Periode: {{ $startDate->format('d M Y') }} – {{ $endDate->format('d M Y') }}</div>
        </div>
        <div class="print-info">
            Dicetak: {{ now()->format('d M Y H:i') }}<br>
            Oleh: {{ auth()->user()->name ?? 'Admin' }}<br>
            Total Data: {{ number_format($stats['total']) }} pesanan
        </div>
    </div>
</div>

{{-- Stat Boxes --}}
<div class="stats-grid">
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-val">{{ number_format($stats['total']) }}</div>
            <div class="stat-lbl">Total Pesanan</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">Rp {{ number_format($stats['pendapatan'], 0, ',', '.') }}</div>
            <div class="stat-lbl">Total Pendapatan</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">{{ number_format($stats['selesai']) }}</div>
            <div class="stat-lbl">Pesanan Selesai</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">{{ number_format($stats['dibatalkan']) }}</div>
            <div class="stat-lbl">Pesanan Dibatalkan</div>
        </div>
    </div>
</div>

{{-- Status Summary --}}
<div class="section-title">Rekap per Status</div>
@php
    $statusLabels = [
        'pending'   => ['Menunggu',       'badge-warning'],
        'paid'      => ['Dibayar',         'badge-info'],
        'confirmed' => ['Dikonfirmasi',    'badge-info'],
        'ongoing'   => ['Sedang Berjalan', 'badge-success'],
        'completed' => ['Selesai',         'badge-success'],
        'returned'  => ['Dikembalikan',    'badge-info'],
        'cancelled' => ['Dibatalkan',      'badge-danger'],
        'penalty'   => ['Denda',           'badge-danger'],
    ];
@endphp
<table class="status-table">
    <thead>
        <tr><th>Status</th><th>Jumlah Pesanan</th></tr>
    </thead>
    <tbody>
        @foreach($perStatus as $status => $count)
        @php [$label, $badge] = $statusLabels[$status] ?? [ucfirst($status), 'badge-secondary']; @endphp
        <tr>
            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
            <td><strong>{{ number_format($count) }}</strong> pesanan</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Orders Table --}}
<div class="section-title">Detail Pesanan</div>
<table class="orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Kode Pesanan</th>
            <th>Customer</th>
            <th>Produk</th>
            <th>Toko</th>
            <th>Tanggal</th>
            <th>Metode</th>
            <th>Status</th>
            <th class="text-right">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $i => $order)
        @php [$label, $badge] = $statusLabels[$order->status] ?? [ucfirst($order->status), 'badge-secondary']; @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $order->order_code }}</strong></td>
            <td>{{ $order->user->name ?? '-' }}</td>
            <td>{{ Str::limit($order->productRental->product->name ?? '-', 25) }}</td>
            <td>{{ Str::limit($order->productRental->product->shop->name_store ?? '-', 18) }}</td>
            <td>{{ $order->created_at->format('d/m/Y') }}</td>
            <td>{{ $order->delivery_method === 'delivery' ? 'Antar' : 'Ambil' }}</td>
            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
            <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; color:#9ca3af; padding:16px;">Tidak ada data pesanan</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dokumen ini digenerate secara otomatis oleh sistem {{ $appName }} pada {{ now()->format('d M Y H:i:s') }} WIB
</div>

</body>
</html>
