@php
$setting = \App\Models\Setting::first();
@endphp

<header class="mobile-top-header">
    <div class="header-top-row">
        <div class="app-branding">
            @if(!Request::routeIs('kurir.dashboard'))
            <a href="{{ route('kurir.dashboard') }}" class="header-icon-btn" style="margin-right: 8px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            @endif

            @if ($setting && $setting->logo)
            <div class="app-logo-box">
                <img src="{{ asset('storage/' . $setting->logo) }}" class="app-logo-img" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            @else
            <div class="app-logo-box">
                <i class="fas fa-motorcycle"></i>
            </div>
            @endif

            <span class="app-title">{{ $setting->app_name ?? 'MyShop' }}</span>
        </div>
        <div class="header-icons">
            <div class="notification-wrapper" style="position: relative;">
                <button type="button" class="header-icon-btn notification-btn" id="courierNotificationBtn" style="border: none; background: none; cursor: pointer; position: relative;">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="courierNotifBadge" style="display: none; position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 10px;">0</span>
                </button>

                <div class="notification-dropdown" id="courierNotificationDropdown" style="display: none; position: absolute; right: 0; top: 100%; width: 300px; background: white; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; z-index: 1000; overflow: hidden;">
                    <div class="dropdown-header" style="padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: bold; background: #f8f9fa;">
                        Notifikasi
                    </div>
                    <div class="dropdown-body" id="courierAllNotificationsList" style="max-height: 300px; overflow-y: auto;">
                        <div class="loading-state" style="padding: 20px; text-align: center;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat...
                        </div>
                    </div>
                </div>
            </div>

            <a href="#" class="header-icon-btn">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>
</header>

{{-- Load Courier Notification Script --}}
<script src="{{ asset('js/courier-notification.js') }}"></script>

<style>
    .notification-dropdown.active {
        display: block !important;
    }
</style>