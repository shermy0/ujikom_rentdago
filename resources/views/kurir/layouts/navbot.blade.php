  <!-- Bottom Navbar (Green Theme for kurir) -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('kurir.dashboard') }}" class="nav-item {{ Request::routeIs('kurir.dashboard') ? 'active' : '' }}">
            <i class="fa fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('kurir.orders') }}" class="nav-item {{ Request::routeIs('kurir.orders') ? 'active' : '' }}">
            <i class="fa fa-box"></i>
            <span>Pesanan</span>
        </a>
      
        <a href="{{ route('kurir.history') }}" class="nav-item {{ Request::routeIs('kurir.history') ? 'active' : '' }}">
            <i class="fa fa-history"></i>
            <span>Riwayat</span>
        </a>
        <a href="{{ route('kurir.profile') }}" class="nav-item {{ Request::routeIs('kurir.profile') ? 'active' : '' }}">
            <i class="fa fa-user"></i>
            <span>Saya</span>
        </a>
    </nav>