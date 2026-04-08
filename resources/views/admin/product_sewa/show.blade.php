@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-eye me-2"></i>Detail Produk Sewa
            </h5>
            <div>
                <a href="{{ route('admin.product_sewa.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Product Info Section -->
            <div class="section-title">
                <i class="bi bi-box-seam"></i> Informasi Produk
            </div>
            <div class="info-card mb-4">
                <div class="row">
                    <div class="col-md-3">
                        @if ($rental->product->images->count() > 0)
                            <img src="{{ asset('storage/' . $rental->product->images->first()->image_path) }}"
                                alt="{{ $rental->product->name }}"
                                class="product-image">
                        @else
                            <div class="product-image-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="150"><strong>Nama Produk</strong></td>
                                    <td>: {{ $rental->product->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kode Produk</strong></td>
                                    <td>: <code>{{ $rental->product->code }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Kategori</strong></td>
                                    <td>: <span class="badge bg-info">{{ $rental->product->category->name ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Kondisi</strong></td>
                                    <td>: {{ $rental->product->condition ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>:
                                        @if ($rental->product->is_maintenance)
                                            <span class="badge bg-danger">Maintenance</span>
                                        @else
                                            <span class="badge bg-success">Tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shop Info Section -->
            <div class="section-title">
                <i class="bi bi-shop"></i> Informasi Toko
            </div>
            <div class="info-card mb-4">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="150"><strong>Nama Toko</strong></td>
                            <td>: {{ $rental->product->shop->name_store ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Alamat</strong></td>
                            <td>: {{ $rental->product->shop->address_store ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Toko</strong></td>
                            <td>:
                                @if ($rental->product->shop && $rental->product->shop->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Rental Info Section -->
            <div class="section-title">
                <i class="bi bi-cart-check"></i> Informasi Sewa
            </div>
            <div class="info-card mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="price-box price-box-primary">
                            <div class="price-label">Harga Sewa</div>
                            <div class="price-value text-success">{{ $rental->formatted_price }}</div>
                            <div class="price-cycle">per {{ $rental->cycle_value }} Jam</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="price-box price-box-danger">
                            <div class="price-label">Denda Keterlambatan</div>
                            <div class="price-value text-danger">{{ $rental->formatted_penalties_price }}</div>
                            <div class="price-cycle">per {{ $rental->penalties_cycle_value }} Jam</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Info Section -->
            <div class="section-title">
                <i class="bi bi-truck"></i> Metode Pengiriman
            </div>
            <div class="info-card mb-4">
                @php
                    $deliveryType = $rental->getRawOriginal('is_delivery');
                @endphp
                <div class="d-flex gap-3 mb-3">
                    @if ($deliveryType === 'pickup' || $deliveryType === 'pickup_delivery')
                        <span class="delivery-badge pickup">
                            <i class="bi bi-person-walking"></i> Ambil Sendiri (Pickup)
                        </span>
                    @endif
                    @if ($deliveryType === 'delivery' || $deliveryType === 'pickup_delivery')
                        <span class="delivery-badge delivery">
                            <i class="bi bi-truck"></i> Antar (Delivery)
                        </span>
                    @endif
                </div>

                @if ($rental->pickup_address)
                    <div class="mt-3">
                        <strong>Alamat Pickup:</strong>
                        <p class="mb-0 text-muted">{{ $rental->pickup_address }}</p>
                    </div>
                @endif
            </div>
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

            .btn-primary {
                background: linear-gradient(135deg, #ee4d2d, #ff6b35);
                border: none;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #d94429, #e55a2b);
            }

            .section-title {
                font-weight: 600;
                color: #333;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 2px solid #ee4d2d;
                display: inline-block;
            }

            .section-title i {
                color: #ee4d2d;
            }

            .info-card {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                border: 1px solid #e9ecef;
            }

            .product-image {
                width: 100%;
                max-width: 150px;
                height: 150px;
                object-fit: cover;
                border-radius: 10px;
            }

            .product-image-placeholder {
                width: 150px;
                height: 150px;
                background: #e9ecef;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #adb5bd;
                font-size: 3rem;
            }

            .price-box {
                background: white;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
                border: 1px solid #e9ecef;
            }

            .price-label {
                color: #666;
                font-size: 14px;
                margin-bottom: 5px;
            }

            .price-value {
                font-size: 24px;
                font-weight: 700;
            }

            .price-cycle {
                color: #999;
                font-size: 13px;
            }

            .delivery-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                border-radius: 8px;
                font-weight: 500;
            }

            .delivery-badge.pickup {
                background: #e3f2fd;
                color: #1976d2;
            }

            .delivery-badge.delivery {
                background: #f3e5f5;
                color: #7b1fa2;
            }

            code {
                color: #666;
                background: #fff;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
            }
        </style>
    @endpush
@endsection
