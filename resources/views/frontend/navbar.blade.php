@php
$setting = \App\Models\Setting::first();
@endphp

<!-- Header Shopee Integrated - NO GAP -->
<header class="shopee-navbar-integrated">
    <div class="navbar-top-section">
        <div class="app-branding">
            @if ($setting && $setting->logo)
            <div class="app-logo-box">
                <img src="{{ asset('storage/' . $setting->logo) }}" class="app-logo-img" alt="Logo">
            </div>
            @else
            <div class="app-logo-box">
                <i class="fas fa-store"></i>
            </div>
            @endif
            <span class="app-title">{{ $setting->app_name ?? 'MyShop' }}</span>        
        </div>

        <div class="header-icons">
            {{-- Tombol switch hanya untuk seller --}}
            @auth
            @if (Auth::user()->role === 'seller')
            <form method="POST" action="{{ route('account.switch') }}" class="m-0">
                @csrf
                <button type="submit" class="header-icon-btn" title="Beralih Akun">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </form>
            @endif
            @endauth

            @auth
            {{-- Notification Button - Berbeda untuk Seller dan Customer --}}
            @if(Auth::user()->role === 'seller')
            @if(session('account_mode', 'seller') === 'seller')
            {{-- Seller Mode Button --}}
            <button type="button" class="header-icon-btn notification-btn" id="notificationBtn" title="Notifikasi Penjual">
                <i class="fas fa-bell"></i>
                <span class="notification-badge badge-notif" id="notifBadge" style="display: none;">0</span>
            </button>
            @else
            {{-- Customer Mode Button (for Seller) --}}
            <button type="button" class="header-icon-btn notification-btn" id="customerNotificationBtn" title="Notifikasi Belanja">
                <i class="fas fa-bell"></i>
                <span class="notification-badge badge-notif" id="customerNotifBadge" style="display: none;">0</span>
            </button>
            @endif
            @elseif(Auth::user()->role === 'customer')
            <button type="button" class="header-icon-btn notification-btn" id="customerNotificationBtn" title="Notifikasi">
                <i class="fas fa-bell"></i>
                <span class="notification-badge badge-notif" id="customerNotifBadge" style="display: none;">0</span>
            </button>
            @endif
            @endauth
        </div>
    </div>

    @auth
    {{-- Notification Dropdown for Seller --}}
    @if(Auth::user()->role === 'seller' && session('account_mode', 'seller') === 'seller')
    <div class="notification-dropdown seller-notification-dropdown" id="notificationDropdown">
        <div class="notif-dropdown-header">
            <h4><i class="fas fa-store-alt"></i> Notifikasi Penjual</h4>
        </div>
        <div class="notif-dropdown-content">
            <div class="notif-list" id="allNotificationsList">
                <div class="empty-notif">
                    <i class="fas fa-bell"></i>
                    <p>Belum ada notifikasi baru</p>
                </div>
            </div>
        </div>
        <div class="notif-dropdown-footer">
            <a href="{{ route('seller.orders') }}" class="view-all-notif-btn">
                <i class="fas fa-clipboard-list"></i> Lihat Semua Pesanan
            </a>
        </div>
    </div>
    @endif

    {{-- Notification Dropdown for Customer (including Seller in Customer Mode) --}}
    @if(Auth::user()->role === 'customer' || (Auth::user()->role === 'seller' && session('account_mode', 'seller') === 'customer'))
    <div class="notification-dropdown customer-notification-dropdown" id="customerNotificationDropdown">
        <div class="notif-dropdown-header">
            <h4><i class="fas fa-shopping-bag"></i> Notifikasi Pesanan</h4>
        </div>
        <div class="notif-dropdown-content">
            <div class="notif-list" id="customerAllNotificationsList">
                <div class="empty-notif">
                    <i class="fas fa-bell"></i>
                    <p>Belum ada notifikasi baru</p>
                </div>
            </div>
        </div>
        <div class="notif-dropdown-footer">
            <a href="{{ route('customer.order.index') }}" class="view-all-notif-btn">
                <i class="fas fa-box"></i> Lihat Semua Pesanan
            </a>
        </div>
    </div>
    @endif
    @endauth

    <!-- User Welcome Section (jika login) -->
    @auth
    <div class="user-welcome-section">
        @php
        $user = auth()->user();
        @endphp
        <div class="user-profile-mini">
            @if(!empty($user->avatar))
            <img src="{{ asset('storage/' . $user->avatar) }}" class="user-mini-avatar" alt="Avatar">
            @else
            <div class="user-mini-placeholder">
                <i class="fa fa-user"></i>
            </div>
            @endif
            <div class="user-mini-info">
                <span class="greeting-text">Selamat datang</span>
                <strong class="user-name-text">{{ $user->name }}</strong>
                <!-- @if(Auth::user()->role === 'seller')
                <span class="user-role-badge seller-badge"><i class="fas fa-store"></i> Penjual</span>
                @else
                <span class="user-role-badge customer-badge"><i class="fas fa-user"></i> Pembeli</span>
                @endif -->
            </div>
        </div>
    </div>
    @endauth
</header>

<!-- Alert Messages - Seamless Integration -->
@if (!Auth::check() || is_null(Auth::user()->phone_verified_at) || session('success') || session('error'))
<div class="shopee-alert-section">
    @if (!Auth::check())
    <div class="shopee-alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Kamu belum login, silakan login untuk verifikasi nomor HP.</span>
    </div>
    @else
    @if (is_null(Auth::user()->phone_verified_at))
    <div class="shopee-alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Nomor HP belum terverifikasi, silakan verifikasi dulu.</span>
    </div>
    @endif
    @endif

    @if (session('success'))
    <div class="shopee-alert alert-success">
        <i class="fa fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div class="shopee-alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif
</div>
@endif

<style>
    /* ========================================
       SHOPEE NAVBAR INTEGRATED - NO GAP
    ======================================== */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    .shopee-navbar-integrated {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        padding: 12px 16px;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 12px rgba(238, 77, 45, 0.2);
        margin: 0;
    }

    /* Navbar Top Section */
    .navbar-top-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    /* App Branding */
    .app-branding {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .app-logo-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .app-logo-box i {
        font-size: 20px;
        color: #ee4d2d;
    }

    .app-logo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .app-title {
        color: white;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.3px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Header Icons */
    .header-icons {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        padding: 0;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        position: relative;
    }

    .header-icon-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }

    .header-icon-btn i {
        font-size: 18px;
    }

    .notification-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        min-width: 18px;
        height: 18px;
        background: #ff4757;
        border-radius: 10px;
        border: 2px solid white;
        color: white;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        animation: badgePulse 2s infinite;
    }

    @keyframes badgePulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    /* User Welcome Section */
    .user-welcome-section {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 10px 12px;
        margin-top: 8px;
    }

    .user-profile-mini {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-mini-avatar,
    .user-mini-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.5);
        flex-shrink: 0;
    }

    .user-mini-placeholder {
        background: rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .user-mini-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .greeting-text {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 400;
    }

    .user-name-text {
        font-size: 14px;
        color: white;
        font-weight: 600;
    }

    .user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        margin-top: 2px;
        width: fit-content;
    }

    .seller-badge {
        background: rgba(76, 175, 80, 0.9);
        color: white;
    }

    .customer-badge {
        background: rgba(33, 150, 243, 0.9);
        color: white;
    }

    /* ========================================
       ALERT SECTION - SEAMLESS
    ======================================== */
    .shopee-alert-section {
        background: #f8f9fa;
        padding: 8px 16px;
        margin: 0;
    }

    .shopee-alert {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 12px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 6px;
        animation: slideDown 0.3s ease;
    }

    .shopee-alert:last-child {
        margin-bottom: 0;
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

    .shopee-alert i {
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .shopee-alert span {
        flex: 1;
        line-height: 1.4;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 3px solid #ffc107;
    }

    .alert-warning i {
        color: #ffc107;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 3px solid #28a745;
    }

    .alert-success i {
        color: #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 3px solid #dc3545;
    }

    .alert-danger i {
        color: #dc3545;
    }

    /* Form inside header icons */
    .header-icons form {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* ========================================
       NOTIFICATION DROPDOWN - SEPARATED
    ======================================== */
    .notification-dropdown {
        position: absolute;
        top: 60px;
        right: 16px;
        width: 340px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 1000;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .notification-dropdown.active {
        display: flex;
        animation: slideDown 0.3s ease;
    }

    /* SELLER Dropdown Specific */
    .seller-notification-dropdown {
        border-top: 3px solid #4caf50;
    }

    .seller-notification-dropdown .notif-dropdown-header {
        background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
        color: white;
    }

    .seller-notification-dropdown .notif-dropdown-header h4 {
        color: white;
    }

    .seller-notification-dropdown .view-all-notif-btn {
        color: #4caf50;
    }

    .seller-notification-dropdown .view-all-notif-btn:hover {
        background: #f1f8f4;
        color: #45a049;
    }

    /* CUSTOMER Dropdown Specific */
    .customer-notification-dropdown {
        border-top: 3px solid #2196f3;
    }

    .customer-notification-dropdown .notif-dropdown-header {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: white;
    }

    .customer-notification-dropdown .notif-dropdown-header h4 {
        color: white;
    }

    .customer-notification-dropdown .view-all-notif-btn {
        color: #2196f3;
    }

    .customer-notification-dropdown .view-all-notif-btn:hover {
        background: #e3f2fd;
        color: #1976d2;
    }

    /* Common Dropdown Styles */
    .notif-dropdown-header {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notif-dropdown-header h4 {
        margin: 0;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notif-dropdown-header h4 i {
        font-size: 16px;
    }

    .notif-dropdown-content {
        max-height: 400px;
        overflow-y: auto;
    }

    .notif-dropdown-content::-webkit-scrollbar {
        width: 6px;
    }

    .notif-dropdown-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notif-dropdown-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .notif-dropdown-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
    }

    .empty-notif {
        padding: 40px 20px;
        text-align: center;
        color: #999;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .empty-notif i {
        font-size: 40px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .notif-item {
        display: flex;
        gap: 12px;
        padding: 15px;
        border-bottom: 1px solid #f5f5f5;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }

    .notif-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.3s ease;
    }

    .notif-item:hover {
        background: #f8f9fa;
    }

    .notif-item.unread {
        background: #e3f2fd;
    }

    .notif-item.unread::before {
        background: #2196f3;
    }

    .seller-notification-dropdown .notif-item.unread {
        background: #f1f8f4;
    }

    .seller-notification-dropdown .notif-item.unread::before {
        background: #4caf50;
    }

    .notif-item:hover::before {
        background: #ee4d2d;
    }

    .notif-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notif-icon i {
        font-size: 18px;
    }

    /* Customer Notification Icons */
    .notif-icon.pending {
        background: #fff3cd;
        color: #856404;
        box-shadow: 0 2px 8px rgba(133, 100, 4, 0.2);
    }

    .notif-icon.success {
        background: #d4edda;
        color: #155724;
        box-shadow: 0 2px 8px rgba(21, 87, 36, 0.2);
    }

    .notif-icon.transit {
        background: #cfe2ff;
        color: #084298;
        box-shadow: 0 2px 8px rgba(8, 66, 152, 0.2);
    }

    .notif-icon.arrived {
        background: #fff4e5;
        color: #e65100;
        box-shadow: 0 2px 8px rgba(230, 81, 0, 0.2);
    }

    .notif-icon.completed {
        background: #d1e7dd;
        color: #0f5132;
    }

    .notif-icon.cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .notif-icon.order {
        background: #e3f2fd;
        color: #1976d2;
    }

    /* Seller Notification Icons */
    .notif-icon.chat {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .notif-icon.courier {
        background: #e8f5e9;
        color: #388e3c;
    }

    .notif-icon.courier-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .notif-icon.courier-accepted {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .notif-icon.courier-delivered {
        background: #fff9c4;
        color: #f57f17;
    }

    .notif-icon.courier-return {
        background: #fce4ec;
        color: #c2185b;
    }

    .notif-icon.courier-transit {
        background: #e0f2f1;
        color: #00695c;
    }

    /* Icon Pulse Animation for Urgent Notifications */
    @keyframes iconPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }
    }

    .notif-icon.pending i,
    .notif-icon.arrived i {
        animation: iconPulse 2s infinite;
    }

    .notif-body {
        flex: 1;
    }

    .notif-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .notif-desc {
        font-size: 13px;
        color: #666;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .notif-time {
        font-size: 11px;
        color: #999;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .notif-dropdown-footer {
        padding: 12px;
        text-align: center;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
    }

    .view-all-notif-btn {
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    /* ========================================
       RESPONSIVE
    ======================================== */
    @media (max-width: 480px) {
        .notification-dropdown {
            width: calc(100vw - 24px) !important;
            right: 12px !important;
            max-height: 70vh;
        }

        .notif-dropdown-content {
            max-height: calc(70vh - 140px);
        }

        .notif-item {
            padding: 12px;
        }

        .notif-icon {
            width: 38px;
            height: 38px;
        }

        .notif-icon i {
            font-size: 16px;
        }

        .notif-title {
            font-size: 13px;
        }

        .notif-desc {
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .app-title {
            font-size: 16px;
        }

        .app-logo-box {
            width: 36px;
            height: 36px;
        }

        .header-icon-btn {
            width: 34px;
            height: 34px;
        }

        .header-icon-btn i {
            font-size: 16px;
        }

        .user-mini-avatar,
        .user-mini-placeholder {
            width: 32px;
            height: 32px;
        }

        .user-name-text {
            font-size: 13px;
        }
    }

    /* ========================================
       SMOOTH TRANSITIONS
    ======================================== */
    .shopee-navbar-integrated,
    .user-welcome-section,
    .shopee-alert-section {
        transition: all 0.3s ease;
    }
</style>

@auth
@if(auth()->user()->role === 'seller')
@if(session('account_mode', 'seller') === 'seller')
<script src="{{ asset('js/seller-notification.js') }}?v={{ time() }}"></script>
@else
<script src="{{ asset('js/customer-notification.js') }}?v={{ time() }}"></script>
@endif
@elseif(auth()->user()->role === 'customer')
<script src="{{ asset('js/customer-notification.js') }}?v={{ time() }}"></script>
@endif
@endauth