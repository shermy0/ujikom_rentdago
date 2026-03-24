@extends('admin.layouts.app')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-receipt me-2"></i>Detail Pemesanan
            </h5>
        </div>
        <div class="card-body">
            <!-- Order Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-card">
                        <h6 class="info-title">Informasi Order</h6>
                        <table class="info-table">
                            <tr>
                                <td class="label">Kode Order</td>
                                <td class="value"><strong>{{ $order->order_code }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal Order</td>
                                <td class="value">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Status Order</td>
                                <td class="value">
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
                            </tr>
                            <tr>
                                <td class="label">Status Pembayaran</td>
                                <td class="value">
                                    <span class="badge bg-{{ $order->payment?->payment_status == 'paid' ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $order->payment?->payment_status == 'paid' ? 'credit-card-fill' : 'clock' }}"></i>
                                        {{ $order->payment?->payment_status == 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                </td>
                            </tr>
                            @if($order->paid_at)
                            <tr>
                                <td class="label">Tanggal Bayar</td>
                                <td class="value">{{ $order->paid_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h6 class="info-title">Informasi Customer</h6>
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama</td>
                                <td class="value">{{ $order->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Telepon</td>
                                <td class="value">{{ $order->user->phone ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="info-card mb-4">
                <h6 class="info-title">Informasi Produk</h6>
                <div class="row">
                    <div class="col-md-2">
                        @if($order->productRental->product->images->count() > 0)
                            <img src="{{ asset('storage/' . $order->productRental->product->images->first()->image_path) }}" 
                                 alt="{{ $order->productRental->product->name }}"
                                 class="product-image">
                        @else
                            <div class="product-image-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-10">
                        <table class="info-table">
                            <tr>
                                <td class="label" style="width: 200px;">Nama Produk</td>
                                <td class="value"><strong>{{ $order->productRental->product->name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Toko</td>
                                <td class="value">
                                    <span class="badge bg-info">{{ $order->productRental->product->shop->name_store }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Paket Sewa</td>
                                <td class="value">{{ $order->productRental->cycle_value }} Jam</td>
                            </tr>
                            <tr>
                                <td class="label">Harga Sewa</td>
                                <td class="value"><strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rental Info -->
            <div class="info-card mb-4">
                <h6 class="info-title">Informasi Sewa</h6>
                <table class="info-table">
                    <tr>
                        <td class="label" style="width: 200px;">Metode Pengiriman</td>
                        <td class="value">
                            <span class="badge bg-{{ $order->delivery_method == 'delivery' ? 'primary' : 'secondary' }}">
                                <i class="bi bi-{{ $order->delivery_method == 'delivery' ? 'truck' : 'box-seam' }}"></i>
                                {{ $order->delivery_method == 'delivery' ? 'Antar' : 'Ambil Sendiri' }}
                            </span>
                        </td>
                    </tr>
                    @if($order->delivery_method == 'delivery' && $order->customer_delivery_address)
                    <tr>
                        <td class="label">Alamat Pengiriman</td>
                        <td class="value">{{ $order->customer_delivery_address }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Waktu Mulai</td>
                        <td class="value">{{ $order->start_time ? $order->start_time->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Waktu Selesai</td>
                        <td class="value">{{ $order->end_time ? $order->end_time->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- QR Code -->
            @if($order->qr_code && $order->payment?->payment_status == 'paid')
            <div class="info-card">
                <h6 class="info-title">QR Code</h6>
                <div class="text-center">
                    <img src="{{ asset('storage/' . $order->qr_code) }}" 
                         alt="QR Code {{ $order->order_code }}"
                         class="qr-code">
                    <p class="text-muted mt-2 mb-0">QR Code untuk pengambilan/pengembalian barang</p>
                </div>
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

            .info-card {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
            }

            .info-title {
                font-weight: 600;
                color: #333;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 2px solid #dee2e6;
            }

            .info-table {
                width: 100%;
            }

            .info-table tr {
                border-bottom: 1px solid #e9ecef;
            }

            .info-table tr:last-child {
                border-bottom: none;
            }

            .info-table td {
                padding: 12px 0;
            }

            .info-table .label {
                color: #666;
                font-weight: 500;
                width: 180px;
            }

            .info-table .value {
                color: #333;
            }

            .badge {
                font-weight: 500;
                padding: 6px 12px;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }

            .badge i {
                font-size: 12px;
            }

            .product-image {
                width: 100%;
                height: 150px;
                object-fit: cover;
                border-radius: 8px;
            }

            .product-image-placeholder {
                width: 100%;
                height: 150px;
                background: linear-gradient(135deg, #ee4d2d, #ff6b35);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 48px;
            }

            .qr-code {
                max-width: 300px;
                border: 2px solid #dee2e6;
                border-radius: 8px;
                padding: 10px;
                background: white;
            }
        </style>
    @endpush
@endsection
