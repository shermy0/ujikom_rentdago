@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/shop-profile.css') }}">

<style>
.voucher-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.voucher-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.voucher-card {
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    border-radius: 12px;
    padding: 20px;
    color: white;
    position: relative;
    overflow: hidden;
}

.voucher-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.voucher-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
}

.voucher-discount {
    font-size: 28px;
    font-weight: 700;
}

.voucher-type {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.voucher-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}

.voucher-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}

.voucher-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.voucher-code {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    letter-spacing: 1px;
    font-size: 14px;
}

.btn-claim {
    background: white;
    color: #ff6b35;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-claim:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-claim:disabled {
    background: rgba(255, 255, 255, 0.5);
    cursor: not-allowed;
}

.voucher-claimed {
    background: rgba(16, 185, 129, 0.2);
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.empty-vouchers {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}
</style>

<div class="shop-profile-container">

    {{-- HEADER --}}
    <div class="shop-header">
        <a href="{{ url()->previous() }}" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Profil Toko</div>
        <div class="header-spacer"></div>
    </div>

    {{-- SHOP INFO CARD --}}
    <div class="shop-banner">
        <div class="shop-info-section">
            
            {{-- LOGO TOKO --}}
            <div class="shop-logo-large">
                @if($shop->logo)
                    <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name_store }}">
                @else
                    <div class="shop-logo-placeholder-large">
                        <i class="fa-solid fa-store"></i>
                    </div>
                @endif
            </div>

            {{-- INFO TOKO --}}
            <div class="shop-details">
                <h2 class="shop-name-large">{{ $shop->name_store }}</h2>
                
                <div class="shop-status-badge {{ $shop->is_active ? 'active' : 'inactive' }}">
                    <i class="fa-solid fa-circle"></i>
                    {{ $shop->is_active ? 'Toko Buka' : 'Toko Tutup' }}
                </div>

                <div class="shop-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-box"></i>
                        <span>{{ $totalProducts }} Produk</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ $shop->address_store }}</span>
                    </div>
                </div>

                @if($shop->description)
                    <div class="shop-description">
                        <p>{{ $shop->description }}</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- VOUCHER SECTION --}}
    @if($vouchers->count() > 0)
    <div class="voucher-section">
        <div class="section-header">
            <h3><i class="fa-solid fa-ticket"></i> Voucher Tersedia</h3>
            <span class="product-count">{{ $vouchers->count() }} voucher</span>
        </div>

        <div class="voucher-grid">
            @foreach($vouchers as $voucher)
                <div class="voucher-card">
                    <div class="voucher-header">
                        <div class="voucher-discount">
                            {{ $voucher->formatted_discount }}
                        </div>
                        <div class="voucher-type">
                            {{ $voucher->discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}
                        </div>
                    </div>

                    <div class="voucher-name">{{ $voucher->name }}</div>

                    <div class="voucher-info">
                        @if($voucher->min_transaction > 0)
                            <div><i class="fa fa-shopping-cart"></i> Min. Rp {{ number_format($voucher->min_transaction, 0, ',', '.') }}</div>
                        @endif
                        
                        @if($voucher->max_discount)
                            <div><i class="fa fa-arrow-down"></i> Maks. potongan Rp {{ number_format($voucher->max_discount, 0, ',', '.') }}</div>
                        @endif

                        @if($voucher->valid_until)
                            <div><i class="fa fa-clock"></i> Berlaku s/d {{ $voucher->valid_until->format('d M Y') }}</div>
                        @endif

                        @if($voucher->remaining_usage)
                            <div><i class="fa fa-users"></i> Tersisa {{ $voucher->remaining_usage }} kuota</div>
                        @endif
                    </div>

                    <div class="voucher-footer">
                        <div class="voucher-code">{{ $voucher->code }}</div>
                        
                        @auth
                            @if(in_array($voucher->id, $claimedVoucherIds))
                                <div class="voucher-claimed">
                                    <i class="fa fa-check-circle"></i> Terklaim
                                </div>
                            @else
                                <form action="{{ route('customer.vouchers.claim', $voucher->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-claim">Klaim</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-claim">Login untuk Klaim</a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- SECTION TITLE --}}
    <div class="section-header">
        <h3>Produk Toko</h3>
        <span class="product-count">{{ $totalProducts }} produk</span>
    </div>

    {{-- PRODUCT GRID --}}
    @if($shop->products->count() > 0)
        <div class="product-grid">
            @foreach($shop->products as $product)
              <a href="{{ route('customer.product.detail', [
    'slug' => $shop->slug,
    'product' => $product->id
]) }}" class="product-card">

                    
                    {{-- PRODUCT IMAGE --}}
                    <div class="product-image">
                        @php
                            $categoryIcons = [
                                'Kamera' => 'fa-camera',
                                'Elektronik' => 'fa-tv',
                                'Alat' => 'fa-screwdriver-wrench',
                                'Outdoor' => 'fa-campground',
                            ];
                            $icon = $categoryIcons[$product->category->name] ?? 'fa-box';
                        @endphp

                        @if($product->images->count())
                            <img src="{{ asset($product->images->first()->image_path) }}" 
                                 alt="{{ $product->name }}">
                        @else
                            <div class="product-placeholder">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                        @endif

                        {{-- BADGE --}}
                        @if($product->condition)
                            <span class="product-badge">{{ $product->condition }}</span>
                        @endif
                    </div>

                    {{-- PRODUCT INFO --}}
                    <div class="product-info">
                        <h4 class="product-name">{{ $product->name }}</h4>
                        
                        <div class="product-category">
                            <i class="fa-solid fa-tag"></i>
                            {{ $product->category->name }}
                        </div>

                        @if($product->rentals->count() > 0)
                            <div class="product-price">
                                Mulai dari <strong>Rp {{ number_format($product->rentals->min('price')) }}</strong>
                            </div>
                        @endif

                        @if($product->is_maintenance)
                            <span class="maintenance-badge">
                                <i class="fa fa-triangle-exclamation"></i>
                                Maintenance
                            </span>
                        @endif
                    </div>

                </a>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fa-solid fa-box-open"></i>
            <h3>Belum Ada Produk</h3>
            <p>Toko ini belum menambahkan produk untuk disewakan</p>
        </div>
    @endif

</div>
<style>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        showConfirmButton: true
    });
</script>
@endif

/* Mobile responsive */
</style>
@endsection
