@php
    $setting = \App\Models\Setting::first();
@endphp

<aside class="sidebar">
    <!-- Sidebar Header -->
<div class="sidebar-header">
    <div class="sidebar-logo">
        @if($setting && $setting->logo)
            <img 
                src="{{ asset('storage/' . $setting->logo) }}" 
                alt="Logo"
                style="width: 100%; height: 100%; object-fit: cover; border-radius: 30%;">
        @else
            <i class="bi bi-shop"></i>
        @endif
    </div>

    <h4 class="sidebar-title">
        {{ $setting->app_name ?? 'Rental Store' }}
    </h4>
</div>

    <!-- Sidebar Menu -->
    <nav class="sidebar-menu">
        <div class="menu-item">
            <a href="{{ route('admin.dashboard') }}"
                class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 menu-icon"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- <div class="menu-item">
            <a href="{{ route('admin.profile.index') }}"
                class="menu-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle menu-icon"></i>
                <span>Profil Saya</span>
            </a>
        </div> --}}

        <div class="menu-item">
            <a href="{{ route('admin.users.index') }}"
                class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people menu-icon"></i>
                <span>Data User</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.seller-requests.index') }}"
                class="menu-link {{ request()->routeIs('admin.seller-requests.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check menu-icon"></i>
                <span>Request Seller</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.shops.index') }}"
                class="menu-link {{ request()->routeIs('admin.shops.index') || request()->routeIs('admin.shops.show') || request()->routeIs('admin.shops.edit') || request()->routeIs('admin.shops.create') ? 'active' : '' }}">
                <i class="bi bi-shop-window menu-icon"></i>
                <span>Data Toko</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.shops.map') }}"
                class="menu-link {{ request()->routeIs('admin.shops.map') ? 'active' : '' }}">
                <i class="bi bi-map menu-icon"></i>
                <span>Peta Toko Seller</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.categories.index') }}"
                class="menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags menu-icon"></i>
                <span>Kategori Barang</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.products.index') }}"
                class="menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam menu-icon"></i>
                <span>Data Barang</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.product_sewa.index') }}"
                class="menu-link {{ request()->routeIs('admin.product_sewa.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check menu-icon"></i>
                <span>Produk Sewa</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.orders.index') }}"
                class="menu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt menu-icon"></i>
                <span>Data Pemesanan</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.reports.index') }}"
                class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line menu-icon"></i>
                <span>Laporan</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="{{ route('admin.settings.index') }}"
                class="menu-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear menu-icon"></i>
                <span>Pengaturan</span>
            </a>
        </div>
    </nav>
</aside>
