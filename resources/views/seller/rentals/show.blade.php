
@extends('frontend.masterseller')

@section('content')
    <style>
        .show-rental-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
        }

        .content-section {
            padding: 1rem;
        }

        .detail-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .detail-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-card-title i {
            color: #A20B0B;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #1565c0;
            margin-bottom: 1rem;
        }

        .info-box i {
            margin-right: 0.5rem;
        }

        /* Product Info */
        .product-showcase {
            text-align: center;
        }

        .product-image-container {
            width: 100%;
            max-width: 300px;
            height: 200px;
            margin: 0 auto 1rem;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image-container i {
            font-size: 3rem;
            color: #6c757d;
        }

        .product-name-display {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .product-code-display {
            font-size: 0.8rem;
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .product-category-display {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #495057;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: #faf8f8;
            border-radius: 8px;
            border-left: 4px solid #A20B0B;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
        }

        .info-value.price {
            color: #28a745;
        }

        .info-value.penalty {
            color: #dc3545;
        }

        .info-subtext {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Delivery Methods */
        .delivery-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .delivery-method-display {
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            background: #fff;
        }

        .delivery-method-display.active {
            border-color: #A20B0B;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        }

        .delivery-method-display .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .delivery-method-display.active .icon {
            color: #A20B0B;
        }

        .delivery-method-display .text {
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
        }

        /* Additional Info */
        .additional-info {
            display: grid;
            gap: 0.75rem;
            font-size: 0.85rem;
        }

        .additional-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .additional-info-label {
            color: #6c757d;
        }

        .additional-info-value {
            font-weight: 600;
            color: #333;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>

    <div class="show-rental-container">
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.rentals.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Detail Paket Sewa
        </div>
        <div class="create-header-spacer"></div>
    </div>

        <div class="content-section">
            <!-- Product Information -->
            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fa fa-box"></i>
                    Informasi Produk
                </div>

                <div class="product-showcase">
                    <div class="product-image-container">
                        @if ($rental->product->images->count() > 0)
                            <img src="{{ asset($rental->product->images->first()->image_path) }}"
                                alt="{{ $rental->product->name }}">
                        @else
                            <i class="fa fa-camera"></i>
                        @endif
                    </div>

                    <div class="product-name-display">{{ $rental->product->name }}</div>
                    <div class="product-code-display">{{ $rental->product->code }}</div>
                    <span class="product-category-display">
                        <i class="fa fa-tag"></i> {{ $rental->product->category->name }}
                    </span>
                </div>

                @if ($rental->product->description)
                    <div class="info-box" style="margin-top: 1rem;">
                        <i class="fa fa-info-circle"></i>
                        <strong>Deskripsi:</strong> {{ $rental->product->description }}
                    </div>
                @endif
            </div>

            <!-- Rental Pricing -->
            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fa fa-tag"></i>
                    Informasi Harga
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Harga Sewa</span>
                        <span class="info-value price">{{ $rental->formatted_price }}</span>
                        <span class="info-subtext">Per {{ $rental->cycle_value }} Jam</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Denda Keterlambatan</span>
                        <span class="info-value penalty">{{ $rental->formatted_penalties_price }}</span>
                        <span class="info-subtext">Per {{ $rental->penalties_cycle_value }} Jam keterlambatan</span>
                    </div>
                </div>

                <div class="info-box">
                    <i class="fa fa-info-circle"></i>
                    Denda akan otomatis dihitung jika penyewa terlambat mengembalikan produk
                </div>
            </div>

            <!-- Delivery Method -->
            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fa fa-truck"></i>
                    Metode Pengiriman
                </div>

                <div class="delivery-methods">
                    <div class="delivery-method-display {{ in_array('pickup', $rental->is_delivery) ? 'active' : '' }}">
                        <div class="icon">
                            <i class="fa fa-walking"></i>
                        </div>
                        <div class="text">Ambil Sendiri</div>
                    </div>

                    <div class="delivery-method-display {{ in_array('delivery', $rental->is_delivery) ? 'active' : '' }}">
                        <div class="icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <div class="text">Antar</div>
                    </div>
                </div>

                <div style="padding: 0.75rem; background: #f8f9fa; border-radius: 8px; font-size: 0.85rem; color: #6c757d;">
                    <i class="fa fa-check-circle" style="color: #28a745; margin-right: 0.5rem;"></i>
                    <strong>Metode tersedia:</strong>
                    @php
                        $deliveryOptions = [];
                        if (in_array('pickup', $rental->is_delivery)) {
                            $deliveryOptions[] = 'Ambil sendiri';
                        }
                        if (in_array('delivery', $rental->is_delivery)) {
                            $deliveryOptions[] = 'Antar';
                        }
                    @endphp
                    {{ implode(' dan ', $deliveryOptions) }}
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('seller.rentals.index') }}" class="btn-large-action btn-large-back">
                <i class="fa fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('seller.rentals.edit', $rental->id) }}" class="btn-large-action btn-large-edit">
                <i class="fa fa-edit"></i>
                <span>Edit</span>
            </a>
            <form action="{{ route('seller.rentals.destroy', $rental->id) }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus paket rental ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-large-action btn-large-delete">
                    <i class="fa fa-trash"></i>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>
@endsection
