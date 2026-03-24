@extends('frontend.masterseller')

@section('content')
<style>
    /* ========================================
       GLOBAL & CONTAINER
       ======================================== */
    .mypage-container {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding: 0;
        margin: 0;
    }
    
    .mypage-container * {
        box-sizing: border-box;
    }
    
    /* ========================================
       HEADER
       ======================================== */
    .mypage-header {
        background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 20px rgba(102, 126, 234, 0.3);
    }
    
    .mypage-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .header-back-btn {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: all 0.3s;
        backdrop-filter: blur(10px);
    }
    
    .header-back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateX(-3px);
    }
    
    .header-title {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .header-settings-btn {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: all 0.3s;
        backdrop-filter: blur(10px);
    }
    
    .header-settings-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }
    
    /* ========================================
       PROFILE CARD
       ======================================== */
    .profile-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }
    
    .profile-avatar-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }
    
    .profile-info {
        flex: 1;
    }
    
    .profile-name {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .profile-phone {
        font-size: 14px;
        color: #718096;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .profile-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .profile-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .badge-role {
        background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
        color: #fff;
    }
    
    .badge-verified {
        background: #48bb78;
        color: #fff;
    }
    
    .badge-pending {
        background: #ed8936;
        color: #fff;
    }
    
    /* ========================================
       MAIN CONTENT
       ======================================== */
    .mypage-content {
        padding: 20px;
    }
    
    /* ========================================
       SECTION CARDS
       ======================================== */
    .section-card {
        background: #fff;
        border-radius: 16px;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }
    
    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .section-header {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .section-icon.account {
        background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
        color: #fff;
    }
    
    .section-icon.shop {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: #fff;
    }
    
    .section-icon.courier {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: #fff;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
    }
    
    .section-body {
        padding: 20px;
    }
    
    /* ========================================
       INFO ROWS
       ======================================== */
    .info-grid {
        display: grid;
        gap: 16px;
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .info-item:hover {
        background: #e9ecef;
    }
    
    .info-item-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: #fff;
        flex-shrink: 0;
    }
    
    .info-item-content {
        flex: 1;
    }
    
    .info-item-label {
        font-size: 12px;
        color: #718096;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-item-value {
        font-size: 15px;
        color: #2d3748;
        font-weight: 600;
    }
    
    /* ========================================
       SHOP SECTION SPECIFIC
       ======================================== */
    .shop-empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    
    .shop-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: #fff;
    }
    
    .shop-empty-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 10px;
    }
    
    .shop-empty-text {
        font-size: 14px;
        color: #718096;
        margin-bottom: 20px;
    }
    
    .btn-create-shop {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
    }
    
    .btn-create-shop:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        color: #fff;
    }
    
    .shop-info-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .shop-logo {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }
    
    .shop-logo-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }
    
    .shop-info-content {
        flex: 1;
    }
    
    .shop-name {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .shop-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .shop-status.active {
        background: #c6f6d5;
        color: #22543d;
    }
    
    .shop-status.inactive {
        background: #fed7d7;
        color: #742a2a;
    }
    
    .shop-status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .shop-status.active .shop-status-indicator {
        background: #48bb78;
    }
    
    .shop-status.inactive .shop-status-indicator {
        background: #f56565;
    }
    
    .shop-admin-lock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fef3c7;
        color: #92400e;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        margin-left: 6px;
        border: 1px solid #fbbf24;
    }
    
    /* ========================================
       TOGGLE BUTTON
       ======================================== */
    .shop-toggle-section {
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }
    
    .btn-toggle-shop {
        width: 100%;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    
    .btn-toggle-shop::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn-toggle-shop:active::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-toggle-shop.active {
        background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(245, 101, 101, 0.3);
    }
    
    .btn-toggle-shop.active:hover {
        box-shadow: 0 6px 20px rgba(245, 101, 101, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-toggle-shop.inactive {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
    }
    
    .btn-toggle-shop.inactive:hover {
        box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-toggle-shop.locked {
        background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(160, 174, 192, 0.3);
    }
    
    .btn-toggle-shop.locked:hover {
        transform: translateY(-2px);
    }
    
    .shop-notice {
        margin-top: 15px;
        padding: 12px 15px;
        border-radius: 10px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .shop-notice.warning {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        color: #92400e;
    }
    
    .shop-notice.danger {
        background: #fef5e7;
        border: 1px solid #f59e0b;
        color: #7c2d12;
    }
    
    .shop-notice i {
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .shop-notice-content strong {
        display: block;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    /* ========================================
       ACTION BUTTONS
       ======================================== */
    .action-buttons {
        display: grid;
        gap: 12px;
    }
    
    .btn-action {
        
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 12px;
        text-decoration: none;
        color: #2d3748;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .btn-action:hover {
        background: #e9ecef;
        border-color: #667eea;
        transform: translateX(5px);
    }
    
    .btn-action-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .btn-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: #fff;
    }
    
    .btn-action-text {
        font-weight: 600;
        font-size: 15px;
    }
    
    .btn-action-arrow {
        color: #a0aec0;
        transition: all 0.3s;
    }
    
    .btn-action:hover .btn-action-arrow {
        color: #667eea;
        transform: translateX(3px);
    }
    
    /* ========================================
       ACCOUNT ACTIONS (LOGOUT & SWITCH)
       ======================================== */
    .account-actions {
        padding: 20px;
        display: grid;
        gap: 12px;
    }
    
    .btn-account-action {
        width: 50%;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    
    .btn-account-action::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn-account-action:active::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-logout {
        background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        color: #fff;
        max-width: 100px;
        box-shadow: 0 4px 15px rgba(245, 101, 101, 0.3);
    }
    
    .btn-logout:hover {
        box-shadow: 0 6px 20px rgba(245, 101, 101, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-switch {
        background: #fff;
        color: #2d3748;
        border: 2px solid #e2e8f0;
    }
    
    .btn-switch:hover {
        border-color: #667eea;
        background: #f7fafc;
        transform: translateY(-2px);
    }
    
    /* ========================================
       ALERTS
       ======================================== */
    .alert {
        margin: 20px;
        padding: 15px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background: #c6f6d5;
        border: 1px solid #48bb78;
        color: #22543d;
    }
    
    .alert-danger {
        background: #fed7d7;
        border: 1px solid #f56565;
        color: #742a2a;
    }
    
    .alert i {
        font-size: 20px;
    }
    
    .alert-content {
        flex: 1;
    }
    
    .btn-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
        transition: opacity 0.3s;
    }
    
    .btn-close:hover {
        opacity: 1;
    }
    
    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 576px) {
        .mypage-header {
            padding: 15px;
        }
        
        .profile-card {
            padding: 15px;
        }
        
        .profile-avatar,
        .profile-avatar-placeholder {
            width: 60px;
            height: 60px;
        }
        
        .section-header {
            padding: 15px;
        }
        
        .section-body {
            padding: 15px;
        }
    }
</style>

<div class="mypage-container">
    
    <!-- Header -->
    <div class="mypage-header">
        <div class="mypage-header-top">
            <a href="{{ route('seller.dashboard.index') }}" class="header-back-btn">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div class="header-title">Profil Saya</div>
            <div style="width: 40px;"></div>
        </div>
        
        <!-- Profile Card -->
        <div class="profile-card">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="profile-avatar">
            @else
                <div class="profile-avatar-placeholder">
                    <i class="fa fa-user" style="font-size: 28px; color: #fff;"></i>
                </div>
            @endif
            
            <div class="profile-info">
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-phone">
                    <i class="fa fa-phone"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                <div class="profile-badges">
                    <span class="profile-badge badge-role">
                        <i class="fa fa-crown"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i>
        <div class="alert-content">{{ session('success') }}</div>
        <button class="btn-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i>
        <div class="alert-content">{{ session('error') }}</div>
        <button class="btn-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    <!-- Main Content -->
    <div class="mypage-content">
        
        <!-- SECTION 1: KELOLA AKUN -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon account">
                    <i class="fa fa-user"></i>
                </div>
                <div class="section-title">Akun Saya</div>
            </div>
            <div class="section-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-item-icon" style="color: #667eea;">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="info-item-content">
                            <div class="info-item-label">Nama Lengkap</div>
                            <div class="info-item-value">{{ $user->name }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item-icon" style="color: #48bb78;">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="info-item-content">
                            <div class="info-item-label">Nomor Telepon</div>
                            <div class="info-item-value">{{ $user->phone }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-item-icon" style="color: #f5576c;">
                            <i class="fa fa-shield-alt"></i>
                        </div>
                        <div class="info-item-content">
                            <div class="info-item-label">Status Verifikasi HP</div>
                            <div class="info-item-value">
                                @if($user->phone_verified_at)
                                    <span style="color: #48bb78;">
                                        <i class="fa fa-check-circle"></i> Terverifikasi
                                    </span>
                                @else
                                    <span style="color: #ed8936;">
                                        <i class="fa fa-clock"></i> Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons" style="margin-top: 20px;">
                    <a href="{{ route('seller.mypage.edit-account') }}" class="btn-action">
                        <div class="btn-action-left">
                            <div class="btn-action-icon" style="color: #667eea;">
                                <i class="fa fa-user-edit"></i>
                            </div>
                            <span class="btn-action-text">Edit Akun</span>
                        </div>
                        <i class="fa fa-chevron-right btn-action-arrow"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- SECTION 2: KELOLA TOKO -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon shop">
                    <i class="fa fa-store"></i>
                </div>
                <div class="section-title">Toko Saya</div>
            </div>
            <div class="section-body">
                @if($shop)
                    <!-- Shop Info -->
                    <div class="shop-info-card">
                        @if($shop->logo)
                            <img src="{{ asset('storage/' . $shop->logo) }}" alt="Logo Toko" class="shop-logo">
                        @else
                            <div class="shop-logo-placeholder">
                                <i class="fa fa-store" style="font-size: 24px; color: #fff;"></i>
                            </div>
                        @endif
                        
                        <div class="shop-info-content">
                            <div class="shop-name">{{ $shop->name_store }}</div>
                            <span class="shop-status {{ $shop->is_active ? 'active' : 'inactive' }}">
                                <span class="shop-status-indicator"></span>
                                {{ $shop->is_active ? 'Toko Aktif' : 'Toko Nonaktif' }}
                            </span>
                            
                            @if(!$shop->is_active && $shop->deactivated_by === 'admin')
                                <span class="shop-admin-lock-badge">
                                    <i class="fa fa-lock"></i>
                                    Dikunci Admin
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Shop Details -->
                    <div class="info-grid">
                        @if($shop->description)
                        <div class="info-item">
                            <div class="info-item-icon" style="color: #f5576c;">
                                <i class="fa fa-align-left"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Deskripsi Toko</div>
                                <div class="info-item-value">{{ Str::limit($shop->description, 100) }}</div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="info-item">
                            <div class="info-item-icon" style="color: #ed8936;">
                                <i class="fa fa-map-marker-alt"></i>
                            </div>
                            <div class="info-item-content">
                                <div class="info-item-label">Alamat Toko</div>
                                <div class="info-item-value">{{ Str::limit($shop->address_store, 80) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Toggle Status -->
                    <div class="shop-toggle-section">
                        @if(!$shop->is_active && $shop->deactivated_by === 'admin')
                            <button type="button" class="btn-toggle-shop locked" onclick="showAdminLockAlert()">
                                <i class="fa fa-lock"></i>
                                <span>Toko Dikunci Admin</span>
                            </button>
                            
                            <div class="shop-notice danger">
                                <i class="fa fa-shield-alt"></i>
                                <div class="shop-notice-content">
                                    <strong>Toko Dinonaktifkan oleh Admin</strong>
                                    Anda tidak dapat mengaktifkan toko sendiri. Silakan hubungi admin untuk mengaktifkan kembali toko Anda.
                                </div>
                            </div>
                        @else
                            <form action="{{ route('seller.mypage.toggle-shop-status') }}" 
                                  method="POST" 
                                  id="toggleShopForm"
                                  onsubmit="return confirmToggle(event, {{ $shop->is_active ? 'true' : 'false' }})">
                                @csrf
                                <button type="submit" class="btn-toggle-shop {{ $shop->is_active ? 'active' : 'inactive' }}">
                                    <i class="fa {{ $shop->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    <span>{{ $shop->is_active ? 'Nonaktifkan Toko' : 'Aktifkan Toko' }}</span>
                                </button>
                            </form>
                            
                            @if(!$shop->is_active)
                            <div class="shop-notice warning">
                                <i class="fa fa-info-circle"></i>
                                <div class="shop-notice-content">
                                    Toko Anda sedang nonaktif. Pembeli tidak dapat melihat toko dan produk Anda.
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                    
                    <!-- Edit Shop Button -->
                    <div class="action-buttons" style="margin-top: 20px;">
                        <a href="{{ route('seller.mypage.edit-shop') }}" class="btn-action">
                            <div class="btn-action-left">
                                <div class="btn-action-icon" style="color: #f5576c;">
                                    <i class="fa fa-store"></i>
                                </div>
                                <span class="btn-action-text">Edit Data Toko</span>
                            </div>
                            <i class="fa fa-chevron-right btn-action-arrow"></i>
                        </a>
                    </div>
                    
                @else
                    <!-- No Shop Yet -->
                    <div class="shop-empty-state">
                        <div class="shop-empty-icon">
                            <i class="fa fa-store"></i>
                        </div>
                        <div class="shop-empty-title">Belum Punya Toko</div>
                        <div class="shop-empty-text">
                            Buat toko sekarang untuk mulai berjualan dan raih pelanggan lebih banyak!
                        </div>
                        <a href="{{ route('seller.mypage.create-shop') }}" class="btn-create-shop">
                            <i class="fa fa-plus-circle"></i>
                            <span>Buka Toko Sekarang</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- SECTION 3: KELOLA KURIR -->
        @if($shop)
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon courier">
                    <i class="fa fa-shipping-fast"></i>
                </div>
                <div class="section-title">Kurir Saya</div>
            </div>
            <div class="section-body">
                <div class="info-item" style="margin-bottom: 15px;">
                    <div class="info-item-icon" style="color: #4facfe;">
                        <i class="fa fa-truck"></i>
                    </div>
                    <div class="info-item-content">
                        <div class="info-item-label">Layanan Pengiriman</div>
                        <div class="info-item-value">Atur layanan kurir untuk toko Anda</div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('seller.couriers.index') }}" class="btn-action">
                        <div class="btn-action-left">
                            <div class="btn-action-icon" style="color: #4facfe;">
                                <i class="fa fa-shipping-fast"></i>
                            </div>
                            <span class="btn-action-text">Kurir Saya</span>
                        </div>
                        <i class="fa fa-chevron-right btn-action-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Account Actions -->
    <div class="account-actions">
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-account-action btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fa fa-sign-out-alt"></i>
                <span>Keluar</span>
            </button>
        </form>

       
    </div>

</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Smooth scroll animation
document.addEventListener('DOMContentLoaded', function() {
    // Auto hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'all 0.3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Toggle shop confirmation
function confirmToggle(event, isActive) {
    event.preventDefault();
    const form = event.target;
    const action = isActive ? 'nonaktifkan' : 'aktifkan';
    
    Swal.fire({
        title: isActive ? 'Nonaktifkan Toko?' : 'Aktifkan Toko?',
        html: isActive 
            ? `<div style="text-align: center;">
                <p style="margin: 0 0 10px 0; color: #718096; font-size: 15px;">
                    Apakah Anda yakin ingin menonaktifkan toko?
                </p>
                <div style="background: #fed7d7; padding: 12px; border-radius: 10px; margin-top: 10px;">
                    <i class="fa fa-exclamation-triangle" style="color: #f56565;"></i>
                    <span style="color: #742a2a; font-size: 14px; margin-left: 5px;">
                        Pembeli tidak akan bisa melihat toko dan produk Anda
                    </span>
                </div>
              </div>`
            : `<p style="margin: 0; color: #718096; font-size: 15px;">
                Apakah Anda yakin ingin mengaktifkan toko kembali?
              </p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Ya, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        cancelButtonText: 'Batal',
        confirmButtonColor: isActive ? '#f56565' : '#48bb78',
        cancelButtonColor: '#a0aec0',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom-popup',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            form.submit();
        }
    });
    
    return false;
}

// Admin lock alert
function showAdminLockAlert() {
    Swal.fire({
        icon: 'warning',
        title: 'Toko Dikunci Admin',
        html: `
            <div style="text-align: center;">
                <p style="margin: 0 0 10px 0; color: #718096; font-size: 15px;">
                    Toko Anda dinonaktifkan oleh administrator.
                </p>
                <div style="background: #fef3c7; padding: 12px; border-radius: 10px; margin-top: 10px;">
                    <i class="fa fa-lock" style="color: #f59e0b;"></i>
                    <span style="color: #92400e; font-size: 14px; margin-left: 5px;">
                        Silakan hubungi admin untuk mengaktifkan kembali
                    </span>
                </div>
            </div>
        `,
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#667eea',
        customClass: {
            popup: 'swal-custom-popup',
            confirmButton: 'swal-custom-confirm'
        }
    });
}

// Add custom styles for SweetAlert
const style = document.createElement('style');
style.textContent = `
    .swal-custom-popup {
        border-radius: 16px !important;
        padding: 20px !important;
    }
    .swal-custom-confirm,
    .swal-custom-cancel {
        border-radius: 10px !important;
        padding: 10px 24px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }
    .swal-custom-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
    }
    .swal-custom-cancel:hover {
        transform: translateY(-2px) !important;
    }
`;
document.head.appendChild(style);
</script>

@endsection