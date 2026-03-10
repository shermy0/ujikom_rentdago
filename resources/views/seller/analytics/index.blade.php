@extends('frontend.masterseller')

@section('content')
<style>
    .analytics-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .page-header p {
        color: #666;
        font-size: 14px;
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .analytics-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .analytics-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 16px;
    }

    .card-icon.revenue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .card-icon.orders {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .card-icon.completed {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .card-icon.ongoing {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }

    .card-title {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .card-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .card-subtitle {
        font-size: 13px;
        color: #999;
    }

    .period-selector {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .period-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    .period-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .period-btn {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
    }

    .period-btn:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
    }

    .period-btn.active {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        border-color: #ff6b6b;
        color: white;
    }

    .stats-detail {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .detail-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-size: 14px;
        color: #666;
    }

    .detail-value {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        color: #ddd;
    }

    .empty-state p {
        font-size: 16px;
        margin-bottom: 8px;
    }
</style>
    <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.dashboard.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Analitik Bisnis
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="analytics-container">
    <!-- Page Header -->
    <div class="page-header">
        <h2>Analitik Bisnis</h2>
        <p>Pantau performa bisnis rental Anda</p>
    </div>

    @if(auth()->user()->shop)
        <!-- Main Analytics Cards -->
        <div class="analytics-grid">
            <!-- Total Pendapatan Keseluruhan -->
            <div class="analytics-card">
                <div class="card-header">
                    <div class="card-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-title">Total Pendapatan</div>
                </div>
                <div class="card-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="card-subtitle">Keseluruhan</div>
            </div>

            <!-- Total Pesanan Completed -->
            <div class="analytics-card">
                <div class="card-header">
                    <div class="card-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-title">Pesanan Selesai</div>
                </div>
                <div class="card-value">{{ $totalCompleted }}</div>
                <div class="card-subtitle">Total pesanan completed</div>
            </div>

            <!-- Pesanan Aktif (Ongoing) -->
            <div class="analytics-card">
                <div class="card-header">
                    <div class="card-icon ongoing">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-title">Pesanan Aktif</div>
                </div>
                <div class="card-value">{{ $totalOngoing }}</div>
                <div class="card-subtitle">Sedang berlangsung</div>
            </div>

            <!-- Total Semua Pesanan -->
            <div class="analytics-card">
                <div class="card-header">
                    <div class="card-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-title">Total Pesanan</div>
                </div>
                <div class="card-value">{{ $totalOrders }}</div>
                <div class="card-subtitle">Semua status</div>
            </div>
        </div>

        <!-- Period Selector -->
        <div class="period-selector">
            <div class="period-title">Pendapatan Berdasarkan Periode</div>
            <div class="period-buttons">
                <a href="{{ route('seller.analytics', ['period' => 'today']) }}" 
                   class="period-btn {{ $period == 'today' ? 'active' : '' }}">
                    Hari Ini
                </a>
                <a href="{{ route('seller.analytics', ['period' => 'week']) }}" 
                   class="period-btn {{ $period == 'week' ? 'active' : '' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('seller.analytics', ['period' => 'month']) }}" 
                   class="period-btn {{ $period == 'month' ? 'active' : '' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('seller.analytics', ['period' => 'year']) }}" 
                   class="period-btn {{ $period == 'year' ? 'active' : '' }}">
                    Tahun Ini
                </a>
            </div>
        </div>

        <!-- Stats Detail -->
        <div class="stats-detail">
            <div class="detail-title">Detail Pendapatan</div>
            
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-calendar-day" style="margin-right: 8px; color: #ff6b6b;"></i>
                    Pendapatan Hari Ini
                </div>
                <div class="detail-value">Rp {{ number_format($revenueToday, 0, ',', '.') }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-calendar-week" style="margin-right: 8px; color: #4facfe;"></i>
                    Pendapatan Minggu Ini
                </div>
                <div class="detail-value">Rp {{ number_format($revenueWeek, 0, ',', '.') }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #43e97b;"></i>
                    Pendapatan Bulan Ini
                </div>
                <div class="detail-value">Rp {{ number_format($revenueMonth, 0, ',', '.') }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-calendar" style="margin-right: 8px; color: #667eea;"></i>
                    Pendapatan Tahun Ini
                </div>
                <div class="detail-value">Rp {{ number_format($revenueYear, 0, ',', '.') }}</div>
            </div>
        </div>

    @else
        <div class="empty-state">
            <i class="fas fa-store-slash"></i>
            <p>Anda belum memiliki toko</p>
            <p style="font-size: 14px; color: #bbb;">Buat toko terlebih dahulu untuk melihat analitik</p>
        </div>
    @endif
</div>
@endsection