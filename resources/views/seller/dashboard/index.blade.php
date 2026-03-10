@extends('frontend.masterseller')

@section('content')
<!-- Welcome Section -->
<div class="welcome-section" style="position: relative;">
    <h2>Selamat Pagi, {{ Auth::user()->name ?? 'Alex' }}!</h2>
    <p>Kelola bisnis rental Anda</p>
    <div class="store-icon">
        <i class="fas fa-store"></i>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-badge">Hari Ini</div>
        <div class="stat-label">Pendapatan</div>
        <div class="stat-value">
            <span style="font-size: 16px; font-weight: 600; opacity: 0.9;">Rp</span>
            <span style="font-size: 22px;">{{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="stat-card rentals">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-badge">Aktif</div>
        <div class="stat-label">Pesanan</div>
        <div class="stat-value">{{ $totalActiveRentals ?? 0 }}</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <button class="carousel-nav carousel-prev" id="prevBtn">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="quick-actions-carousel" id="carouselContainer">
        @if (auth()->user()->shop)
        <a href="{{ route('seller.products.index') }}" class="action-btn">
            <div class="action-icon">
                <i class="fas fa-plus"></i>
            </div>
            <span>Tambah Produk</span>
        </a>
        @else
        <a href="{{ route('seller.products.index') }}" class="action-btn disabled">
            <div class="action-icon">
                <i class="fas fa-lock"></i>
            </div>
            <span>Tambah Produk</span>
        </a>
        @endif

        <a href="{{ route('seller.rentals.index') }}" class="action-btn">
            <div class="action-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <span>Paket Sewa</span>
        </a>

        <a href="{{ route('seller.vouchers.index') }}" class="action-btn">
            <div class="action-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <span>Voucher</span>
        </a>

        <a href="{{route('seller.analytics')}}" class="action-btn">
            <div class="action-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <span>Analisis</span>
        </a>

        {{-- <a href="{{ route('seller.mypage.settings') }}" class="action-btn">
            <div class="action-icon">
                <i class="fas fa-cog"></i>
            </div>
            <span>Pengaturan</span>
        </a> --}}
    </div>

    <button class="carousel-nav carousel-next" id="nextBtn">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<!-- Recent Orders -->
<div class="recent-orders">
    <div class="section-header">
        <h3>Pesanan Terbaru</h3>
        <a href="{{ route('seller.orders') }}">Lihat Semua</a>
    </div>

    @forelse($recentOrders as $order)
    <div class="order-card">
        <div class="product-image">
            @if ($order->productRental->product->images->first())
            <img src="{{ asset('storage/' . $order->productRental->product->images->first()->image_path) }}"
                alt="{{ $order->productRental->product->name }}"
                style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
            @else
            📷
            @endif
        </div>
        <div class="order-info">
            <div class="product-name">{{ $order->productRental->product->name }}</div>
            <div class="order-id">Pesanan {{ $order->order_code }}</div>
            <div class="order-meta">
                <span><i class="fas fa-user"></i> {{ $order->user->name }}</span>
                <span><i class="fas fa-clock"></i>
                    {{ \Carbon\Carbon::parse($order->end_time)->locale('id')->diffForHumans() }}
                </span>
            </div>
        </div>
        <div class="order-status">
            @if ($order->status == 'confirmed')
            <span
                style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                Dikonfirmasi
            </span>
            @elseif($order->status == 'ongoing')
            <span
                style="background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                Berlangsung
            </span>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 40px 20px; color: #999;">
        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
        <p>Belum ada pesanan</p>
    </div>
    @endforelse
</div>

<style>
    .quick-actions {
        margin: 20px 0;
        position: relative;
        overflow: hidden;
    }

    .quick-actions-carousel {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 10px 5px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .quick-actions-carousel::-webkit-scrollbar {
        display: none;
    }

    .quick-actions-carousel .action-btn {
        flex: 0 0 auto;
        min-width: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px 15px;
        background: white;
        border-radius: 12px;
        text-decoration: none;
        color: #333;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .quick-actions-carousel .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .quick-actions-carousel .action-btn.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .action-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
        font-size: 24px;
    }

    .quick-actions-carousel .action-btn span {
        font-size: 13px;
        font-weight: 500;
        text-align: center;
        white-space: nowrap;
    }

    /* Carousel Navigation Buttons */
    .carousel-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: white;
        border: none;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s ease;
        color: #667eea;
        font-size: 16px;
    }

    .carousel-nav:hover {
        background: #667eea;
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .carousel-nav:active {
        transform: translateY(-50%) scale(0.95);
    }

    .carousel-prev {
        left: -5px;
    }

    .carousel-next {
        right: -5px;
    }

    .carousel-nav:disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .carousel-nav {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('carouselContainer');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (!carousel || !prevBtn || !nextBtn) return;

        const scrollAmount = 280; // Sesuaikan dengan lebar card + gap

        // Update button states
        function updateButtons() {
            const scrollLeft = carousel.scrollLeft;
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;

            prevBtn.disabled = scrollLeft <= 0;
            nextBtn.disabled = scrollLeft >= maxScroll - 5; // -5 untuk toleransi
        }

        // Scroll previous
        prevBtn.addEventListener('click', function() {
            carousel.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });

        // Scroll next
        nextBtn.addEventListener('click', function() {
            carousel.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        // Update buttons on scroll
        carousel.addEventListener('scroll', updateButtons);

        // Initial button state
        updateButtons();

        // Update on window resize
        window.addEventListener('resize', updateButtons);
    });
</script>
@endsection
