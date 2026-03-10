<!DOCTYPE html>
<html lang="id">

<head>
    @php
    $appName = \App\Models\Setting::first()?->app_name ?? 'Seller';
    $setting = \App\Models\Setting::first();
    @endphp

    <title>{{ $title ?? 'Seller' }} - {{ $appName }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/masterseller.css') }}?v={{ time() }}">

    @stack('styles')
</head>

<body>

    <div class="mobile-view">

        <!-- Modern Seller Header -->
        <header class="seller-top-header">
            <div class="header-left">
                @if($setting && $setting->logo)
                <div class="logo-circle">
                    <img
                        src="{{ asset('storage/' . $setting->logo) }}"
                        alt="Logo"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 30%;">
                </div>
                @else
                <div class="logo-circle">
                    <i class="fas fa-store"></i>
                </div>
                @endif

                <div class="header-text">
                    <h3>{{ $setting->app_name ?? 'Seller Hub' }}</h3>
                </div>
            </div>

            <div class="header-right">
               
                <!-- Switch to Customer -->
                <form method="POST" action="{{ route('account.switch') }}">
                    @csrf
                    <button type="submit" class="btn btn-switch">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </form>

                <a href="{{ route('seller.chat.index') }}"
                    class="nav-item {{ request()->routeIs('seller.chat.*') ? 'active' : '' }}">
                    <i class="fa fa-comments"></i>

                    @php
                    $shop = Auth::user()->shop ?? null;
                    $unreadChatCount = 0;

                    if ($shop) {
                    $unreadChatCount = \App\Models\Conversation::where('shop_id', $shop->id)
                    ->get()
                    ->sum(function($conversation) {
                    return $conversation->unreadForSeller();
                    });
                    }
                    @endphp
                    @if($unreadChatCount > 0)
                    <span class="badge chat-badge">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                    @endif
                </a>

                 <!-- Notification Bell -->
                <button type="button" class="notification-btn" id="notificationBtn">
                    <i class="fas fa-bell"></i>
                    <span class="badge-notif" id="notifBadge" style="display: none;">0</span>
                </button>

            </div>
        </header>

        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notif-dropdown-header">
                <h4>Notifikasi</h4>
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
                    Lihat Semua Notifikasi
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mobile-content">
            @yield('content')
        </div>

        <nav class="seller-bottom-nav">
            <a href="{{ route('seller.dashboard.index') }}"
                class="nav-item {{ request()->routeIs('seller.dashboard.*') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Beranda</span>
            </a>

            <!-- ✅ ORDER NAV WITH BADGE -->
            <a href="{{ route('seller.rentals.index') }}"
                class="nav-item {{ request()->routeIs('seller.rentals*') ? 'active' : '' }}">
                <i class="fas fa-dollar-sign"></i>
                <span>Paket Sewa</span>
                <!-- Badge will be dynamically added by JS -->
                <span class="badge order-badge" style="display: none;">0</span>
            </a>
            <!-- ✅ ORDER NAV WITH BADGE -->
            <a href="{{ route('seller.orders') }}"
                class="nav-item {{ request()->routeIs('seller.orders*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Pesanan</span>
                <!-- Badge will be dynamically added by JS -->
                <span class="badge order-badge" style="display: none;">0</span>
            </a>

            <!-- ✅ CHAT NAV WITH BADGE -->


            <a href="{{ route('seller.scan.index') }}"
                class="nav-item {{ request()->routeIs('seller.scan.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Scan</span>
            </a>

            <a href="{{ route('seller.mypage.index') }}"
                class="nav-item {{ request()->routeIs('seller.mypage.*') ? 'active' : '' }}">
                <i class="fa fa-user"></i>
                <span>Saya</span>
            </a>
        </nav>

    </div>

    <!-- Loader -->
    <div id="ftco-loader" class="show fullscreen">
        <svg class="circular" width="48px" height="48px">
            <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
            <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#FF6B35" />
        </svg>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Di layout blade (header/footer) --}}
    <script src="{{ asset('js/seller-notification.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/seller-order-badge.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/seller-notification.js') }}?v={{ time() }}"></script>
    <script>
        // Init AOS
        AOS.init({
            duration: 800,
            once: true
        });

        $(document).ready(function() {
            console.log('✅ Master Seller: Page loaded');

            // Force hide loader
            $('#ftco-loader').removeClass('show').addClass('hide');

            // Load chat count
            loadUnreadChatCount();

            // ✅ Manually trigger order badge load
            if (window.SellerOrderBadge) {
                console.log('🎯 Triggering Order Badge refresh');
                window.SellerOrderBadge.loadUnreadCount();
            }
        });

        // Function to load unread chat count
        function loadUnreadChatCount() {
            $.ajax({
                url: '{{ route("seller.chat.unread-count") }}',
                method: 'GET',
                success: function(response) {
                    if (response.count > 0) {
                        updateChatBadge(response.count);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading unread chat count:', xhr);
                }
            });
        }

        // Function to update chat badge
        function updateChatBadge(count) {
            const chatNavItem = $('.seller-bottom-nav a[href*="chat"]');
            let badge = chatNavItem.find('.badge.chat-badge');

            if (count > 0) {
                const displayCount = count > 99 ? '99+' : count;

                if (badge.length === 0) {
                    chatNavItem.append('<span class="badge chat-badge">' + displayCount + '</span>');
                } else {
                    badge.text(displayCount).show();
                }
            } else {
                badge.remove();
            }
        }

        // Poll for updates
        setInterval(function() {
            loadUnreadChatCount();
            if (window.SellerOrderBadge) {
                window.SellerOrderBadge.loadUnreadCount();
            }
        }, 30000);

        // Notyf
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'center',
                y: 'top'
            }
        });

        @if(session('sukses'))
        notyf.success("{{ session('sukses') }}");
        @endif

        @if(session('gagal'))
        notyf.error("{{ session('gagal') }}");
        @endif
    </script>

    @stack('scripts')

    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Akses Ditolak',
            text: '{{ session('
            error ') }}',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
</body>

</html>