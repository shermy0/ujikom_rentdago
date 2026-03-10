@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection

@section('navbot')
    @include('kurir.layouts.navbot')
@endsection

@section('content')
<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="welcome-text">
            <span class="welcome-label">Selamat datang</span>
            <span class="welcome-name">{{ auth()->user()->name ?? 'Kurir' }}</span>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="card-section">
    <div class="row g-3">
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $stats['perlu_diambil'] ?? 0 }}</span>
                    <span class="stat-label">Perlu Respon</span>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $stats['sedang_dikirim'] ?? 0 }}</span>
                    <span class="stat-label">Sedang Kirim</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Menu -->
<div class="menu-grid" style="grid-template-columns: repeat(2, 1fr);">
    <a href="{{ route('kurir.orders') }}" class="menu-item">
        <div class="menu-icon">
            <i class="fas fa-list"></i>
        </div>
        <small>Pesanan</small>
    </a>
    <a href="{{ route('kurir.history') }}" class="menu-item">
        <div class="menu-icon">
            <i class="fas fa-history"></i>
        </div>
        <small>Riwayat</small>
    </a>
</div>

<!-- Recent Activity -->
<div class="card-section">
    <div class="section-title">
        <span>Aktivitas Terbaru</span>
        <a href="{{ route('kurir.orders') }}" class="section-link">Lihat Semua</a>
    </div>

    @if(isset($recentShipment) && $recentShipment)
    <div class="activity-item">
        <div class="activity-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="activity-info">
            <span class="activity-title">#{{ $recentShipment->order->order_code }}</span>
            <span class="activity-status">{{ \App\Models\Shipment::getStatusLabel($recentShipment->status) }}</span>
        </div>
        <span class="activity-time">{{ $recentShipment->updated_at->diffForHumans() }}</span>
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <span>Belum ada aktivitas</span>
    </div>
    @endif
</div>

<style>
    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        padding: 20px 16px;
        margin: 0;
        margin-top: 0px; /* Remove gap with header */
    }

    .welcome-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .welcome-avatar {
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .welcome-text {
        display: flex;
        flex-direction: column;
    }

    .welcome-label {
        font-size: 13px;
        color: rgba(255,255,255,0.8);
    }

    .welcome-name {
        font-size: 18px;
        font-weight: 600;
        color: white;
    }

    /* Stats Cards */
    .stat-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .stat-icon.green {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Activity Item */
    .activity-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        background: rgba(34, 197, 94, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #22c55e;
        font-size: 16px;
        margin-right: 12px;
    }

    .activity-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .activity-title {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
    }

    .activity-status {
        font-size: 12px;
        color: #6b7280;
    }

    .activity-time {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 24px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 32px;
        margin-bottom: 8px;
        display: block;
    }

    .empty-state span {
        font-size: 13px;
    }
</style>
@endsection
