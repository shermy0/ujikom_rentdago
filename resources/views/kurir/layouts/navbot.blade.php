  <!-- Bottom Navbar (Green Theme for kurir) -->
  <nav class="mobile-bottom-nav">
      <a href="{{ route('kurir.dashboard') }}" class="nav-item {{ Request::routeIs('kurir.dashboard') ? 'active' : '' }}">
          <i class="fa fa-home"></i>
          <span>Beranda</span>
      </a>
      <a href="{{ route('kurir.orders') }}" class="nav-item {{ Request::routeIs('kurir.orders') ? 'active' : '' }}" style="position: relative;">
          <i class="fa fa-box"></i>
          <span>Pesanan</span>
          <span class="badge order-badge" style="
                display: none;
                position: absolute;
                top: 5px;
                right: 25px;
                background: #ef4444;
                color: white;
                font-size: 10px;
                padding: 2px 6px;
                border-radius: 10px;
                min-width: 18px;
            ">0</span>
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