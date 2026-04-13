@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-receipt me-2"></i>Daftar Pemesanan
            </h5>
        </div>
        <div class="card-body">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Total Order</h6>
                            <h3>{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Menunggu</h6>
                            <h3>{{ $stats['pending'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Dikonfirmasi</h6>
                            <h3>{{ $stats['confirmed'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Berlangsung</h6>
                            <h3>{{ $stats['ongoing'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="bi bi-check-all"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Selesai</h6>
                            <h3>{{ $stats['completed'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h6>Dibatalkan</h6>
                            <h3>{{ $stats['cancelled'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari kode order atau nama customer..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status Order</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="">Semua Status Bayar</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
                @if (request('search') || request('status') || request('payment_status'))
                    <div class="col-md-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Table -->
            <div class="table-responsive order-table-scroll">
                <table class="table table-hover align-middle order-table text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Order</th>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th>Toko</th>
                            <th>Total</th>
                            <th>Status Order</th>
                            <th>Status Bayar</th>
                            <th>Tanggal</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_code }}</strong>
                                </td>
                                <td>
                                    {{ $order->user->name }}
                                </td>
                                <td>
                                    {{ $order->productRental->product->name }}
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $order->productRental->product->shop->name_store }}</span>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'ongoing' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusIcons = [
                                            'pending' => 'clock-history',
                                            'confirmed' => 'check-circle',
                                            'ongoing' => 'arrow-repeat',
                                            'completed' => 'check-all',
                                            'cancelled' => 'x-circle'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Menunggu',
                                            'confirmed' => 'Dikonfirmasi',
                                            'ongoing' => 'Berlangsung',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        <i class="bi bi-{{ $statusIcons[$order->status] ?? 'question' }}"></i>
                                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->payment?->payment_status == 'paid' ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $order->payment?->payment_status == 'paid' ? 'credit-card-fill' : 'clock' }}"></i>
                                        {{ $order->payment?->payment_status == 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $order->created_at->format('d M Y, H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt display-4 d-block mb-2"></i>
                                    Belum ada data pemesanan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .card {
                border: none;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
                border-radius: 12px;
            }

            .card-header {
                background: white;
                border-bottom: 1px solid #f0f0f0;
                padding: 20px 25px;
                border-radius: 12px 12px 0 0 !important;
            }

            .card-body {
                padding: 25px;
            }

            .stat-card {
                background: white;
                border-radius: 12px;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 15px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
            }

            .stat-info h6 {
                margin: 0;
                font-size: 13px;
                color: #666;
                font-weight: 500;
            }

            .stat-info h3 {
                margin: 5px 0 0 0;
                font-size: 28px;
                font-weight: 700;
                color: #333;
            }

            .table th {
                font-weight: 600;
                color: #666;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge {
                font-weight: 500;
                padding: 6px 10px;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .badge i {
                font-size: 12px;
            }

            .order-table-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-x;
            }

            .order-table {
                min-width: 1000px;
            }

            @media (max-width: 768px) {
                .card-header,
                .card-body {
                    padding: 14px;
                }
            }
        </style>
    @endpush
@endsection
