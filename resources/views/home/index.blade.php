@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<div class="shopee-container" style="max-width:480px; margin: 0 auto; padding: 0;">
<link rel="stylesheet" href="{{ asset('frontend/assets/css/home-customer.css') }}?v={{ time() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  

    {{-- SEARCH BAR SHOPEE --}}
    <div class="shopee-search-section">
        <form method="GET" action="{{ route('home') }}" class="shopee-search-bar">
            <input 
                type="text" 
                name="search"
                class="search-input"
                placeholder="Cari barang"
                value="{{ $search }}"
            >
            <button type="submit" class="search-btn">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    {{-- BANNER SLIDER --}}
    <div class="shopee-banner-section">
        <div class="banner-slider" id="bannerSlider">
            <div class="banner-slide active" style="background: linear-gradient(135deg, #ff6b35, #ff8c61);">
                <div class="banner-content">
                    <h3>Gratis Ongkir</h3>
                    <p>Min. pembelian 0rb</p>
                </div>
            </div>
            <div class="banner-slide" style="background: linear-gradient(135deg, #ee4d2d, #ff6b35);">
                <div class="banner-content">
                    <h3>Flash Sale</h3>
                    <p>Diskon hingga 50%</p>
                </div>
            </div>
            <div class="banner-slide" style="background: linear-gradient(135deg, #f53d2d, #ee4d2d);">
                <div class="banner-content">
                    <h3>Cashback</h3>
                    <p>Setiap transaksi</p>
                </div>
            </div>
        </div>
        <div class="banner-dots" id="bannerDots"></div>
    </div>

    {{-- CATEGORIES SHOPEE STYLE --}}
    <div class="shopee-categories">
        <div class="category-grid">
            <a href="{{ route('home') }}" class="category-item {{ !$categorySlug ? 'active' : '' }}">
                <div class="category-icon-box">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <span class="category-name">Semua</span>
            </a>

            @foreach ($categories as $index => $cat)
            <a href="{{ route('home', ['category' => $cat->slug]) }}" 
               class="category-item {{ (isset($activeParentSlug) && $activeParentSlug == $cat->slug) ? 'active' : '' }}">
                <div class="category-icon-box">
                    @if ($cat->icon)
                        <img src="{{ asset($cat->icon) }}" alt="{{ $cat->name }}">
                    @else
                        <i class="bi bi-box-seam"></i>
                    @endif
                    @if ($cat->children->count() > 0)
                        <span class="category-children-badge">{{ $cat->children->count() }}</span>
                    @endif
                </div>
                <span class="category-name">{{ $cat->name }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- SUB-CATEGORIES CHIPS (Shows if a parent is selected and has children) --}}
    @if(isset($subCategories) && $subCategories->isNotEmpty())
    <div class="shopee-subcategories">
        <a href="{{ route('home', ['category' => $activeParentSlug]) }}"
           class="subcat-chip {{ $categorySlug === $activeParentSlug ? 'active' : '' }}">
           Lihat Semua
        </a>
        @foreach($subCategories as $sub)
        <a href="{{ route('home', ['category' => $sub->slug]) }}"
           class="subcat-chip {{ $categorySlug === $sub->slug ? 'active' : '' }}">
           {{ $sub->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- PRODUCT TABS --}}
    <div class="shopee-tabs">
        <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'all'])) }}"
           class="tab-item {{ $tab === 'all' ? 'active' : '' }}">
            <i class="bi bi-grid"></i> Semua
        </a>
        <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'latest'])) }}"
           class="tab-item {{ $tab === 'latest' ? 'active' : '' }}">
            <i class="bi bi-stars"></i> Terbaru
        </a>
        <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'popular'])) }}"
           class="tab-item {{ $tab === 'popular' ? 'active' : '' }}">
            <i class="bi bi-fire"></i> Terpopuler
        </a>
    </div>

    {{-- PRODUCTS GRID --}}
    <div class="shopee-products">
        <div class="products-grid">
            @forelse ($products as $product)
                <div class="product-card-shopee">
                    @include('home.partials.product-card', ['product' => $product])
                </div>
            @empty
                <div class="empty-state-shopee">
                    <i class="bi bi-inbox"></i>
                    <h6>Tidak ada produk</h6>
                    <p>Coba kata kunci atau kategori lain</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="shopee-pagination">
            {{ $products->links('home.partials.pagination-modern') }}
        </div>
    </div>

</div>

{{-- BACK TO TOP --}}
<button class="back-to-top" id="backToTop">
    <i class="bi bi-chevron-up"></i>
</button>

<script src="{{ asset('frontend/assets/js/shopee-home.js') }}?v={{ time() }}"></script>
@endsection