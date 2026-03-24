@extends('frontend.master')

@section('navbot')
    @include('frontend.navbot')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/protail.css') }}?v={{ time() }}">

<div class="home-container">
    {{-- HEADER DETAIL PRODUK --}}
    <div class="product-detail-header">
        <a href="{{ url()->previous() }}" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Detail Produk</div>
    </div>

    {{-- IMAGE --}}
    <div class="product-image-wrapper mb-4">
        @php
            $categoryIcons = [
                'Kamera' => 'fa-camera',
                'Elektronik' => 'fa-tv',
                'Alat' => 'fa-screwdriver-wrench',
                'Outdoor' => 'fa-campground',
            ];
            $icon = $categoryIcons[$product->category->name] ?? 'fa-box';
        @endphp

        {{-- FOTO UTAMA --}}
        <div class="product-main-image mb-2">
            @if($product->images->count())
                <img id="mainProductImage" src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
            @else
                <div class="product-main-placeholder">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
            @endif
        </div>

        {{-- THUMBNAIL --}}
        @if($product->images->count() > 1)
            <div class="product-thumbnails">
                @foreach($product->images as $image)
                    <img src="{{ asset('storage/' . $image->image_path) }}" class="thumbnail-item" onclick="changeMainImage(this)">
                @endforeach
            </div>
        @endif
    </div>

    {{-- PRODUCT INFO CARD --}}
    <div class="product-info-card">
        {{-- ROW ATAS --}}
        <div class="product-top-row">
            <div class="product-main-info">
                <p class="product-name">{{ $product->name }}</p>
                <span class="product-category">{{ $product->category->name }}</span>
            </div>

            <div class="product-side-info">
                @if($product->condition)
                    <div class="side-chip">
                        <i class="fa fa-circle-info"></i>
                        {{ $product->condition }}
                    </div>
                @endif

                @if($product->is_maintenance)
                    <div class="side-chip warning">
                        <i class="fa fa-triangle-exclamation"></i>
                        Maintenance
                    </div>
                @endif
            </div>
        </div>

        {{-- METODE PENGAMBILAN --}}
        <div class="product-description compact">
            <h6>Metode Pengambilan</h6>
            @php
                $methods = $product->rentals->pluck('is_delivery')->flatten()->unique();
            @endphp

            @if($methods->contains('pickup'))
                <span class="method-chip pickup">
                    <i class="fa fa-store"></i> Pickup
                </span>
            @endif

            @if($methods->contains('delivery'))
                <span class="method-chip delivery">
                    <i class="fa fa-truck"></i> Delivery
                </span>
            @endif
        </div>

        {{-- PAKET SEWA --}}
        <div class="product-description compact" style="padding-top: 12px; border-top: 1px solid #f0f0f0; margin-top: 12px;">
            <h6>Paket Sewa Tersedia</h6>
            @foreach($product->rentals as $rental)
                <div class="rental-package-item">
                    <div class="rental-duration">
                        <i class="fa fa-clock"></i>
                        {{ $rental->cycle_value }} Jam
                    </div>
                    <div class="rental-price">
                        Rp {{ number_format($rental->price) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Shop Info Card --}}
    @if($product->shop)
    <a href="{{ route('customer.shop.profile', !empty($product->shop->slug) ? $product->shop->slug : 'no-shop') }}" class="shop-info-card clickable {{ !$product->shop->is_active ? 'shop-inactive' : '' }}">
        <div class="shop-main">
            <div class="shop-left">
                <div class="shop-logo-wrapper">
                    @if($product->shop->logo)
                        <img src="{{ asset('storage/' . $product->shop->logo) }}" alt="{{ $product->shop->name_store }}">
                    @else
                        <div class="shop-logo-placeholder">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    @endif
                </div>
            </div>

            <div class="shop-center">
                <div class="shop-name">{{ $product->shop->name_store }}</div>
                <div class="shop-status {{ $product->shop->is_active ? 'active' : 'inactive' }}">
                    <i class="fa-solid fa-circle"></i>
                    {{ $product->shop->is_active ? 'Toko Buka' : 'Toko Tutup' }}
                </div>
            </div>

            <div class="shop-right">
                <i class="fa fa-chevron-right"></i>
            </div>
        </div>

        <div class="shop-address">
            <i class="fa-solid fa-location-dot"></i>
            {{ $product->shop->address_store }}
        </div>

        @if(!$product->shop->is_active)
        <div class="shop-inactive-warning">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Toko sedang tidak menerima pesanan</span>
        </div>
        @endif
    </a>
    @endif

    {{-- CTA SEWA --}}
    <div class="rent-cta">
        @if($product->shop && $product->shop->is_active)
            <a href="{{ route('customer.checkout', $product->id) }}" class="rent-btn">
                <i class="fa fa-shopping-cart"></i>
                Lanjut ke Checkout
            </a>
        @else
            <button class="rent-btn disabled" disabled>
                <i class="fa fa-store-slash"></i>
                Toko Sedang Tutup
            </button>
            <p class="rent-disabled-hint">
                <i class="fa fa-info-circle"></i>
                Toko ini sedang tidak menerima pesanan. Coba lagi nanti.
            </p>
        @endif
    </div>
</div>

<script>
// IMAGE PREVIEW
function changeMainImage(el) {
    document.getElementById('mainProductImage').src = el.src;
}
</script>

@endsection