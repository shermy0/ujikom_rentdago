<!DOCTYPE html>
<html lang="id">

<head>
    @php
    $appName = \App\Models\Setting::first()?->app_name ?? 'Kurir';
    @endphp

    <title>{{ $title ?? 'Kurir' }} - {{ $appName }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url('/frontend/assets/images/fujiyama.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 20px;
        }

        .mobile-view {
            position: relative;
            width: 100%;
            max-width: 470px;
            height: 97vh;
            background: #f8f8f8;
            display: flex;
            flex-direction: column;
            overflow: clip; /* clip, bukan hidden, agar sticky tetap bekerja */
        }

        .mobile-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            background: #f8f8f8;
        }

        .mobile-content::-webkit-scrollbar {
            width: 4px;
        }

        .mobile-content::-webkit-scrollbar-thumb {
            background: rgba(34, 197, 94, 0.3);

        }

        /* ========== HEADER MODERN ========== */
        .mobile-top-header {
            position: sticky;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            padding: 10px 16px 14px;
            z-index: 100;
            flex-shrink: 0;
        }

        .header-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .app-branding {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-logo-box {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-logo-box i {
            font-size: 18px;
            color: #22c55e;
        }

        .app-title {
            color: white;
            font-size: 18px;
            font-weight: 600;
            font-style: italic;
            letter-spacing: -0.3px;
        }

        .header-icons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .header-icon-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .header-icon-btn i {
            font-size: 16px;
        }

        /* ========== BOTTOM NAVIGATION ========== */
        .mobile-bottom-nav {
            width: 100%;
            height: 65px;
            background: white;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 999;
            padding: 0 8px;
            flex-shrink: 0;
            /* Tidak perlu sticky/fixed — flex layout sudah push ke bawah */
        }

        .mobile-bottom-nav .nav-item {
            flex: 1;
            text-align: center;
            text-decoration: none;
            color: #999;
            transition: all 0.3s ease;
            padding: 8px;

        }

        .mobile-bottom-nav .nav-item i {
            display: block;
            font-size: 22px;
            margin-bottom: 4px;
        }

        .mobile-bottom-nav .nav-item span {
            display: block;
            font-size: 11px;
            font-weight: 500;
        }

        .mobile-bottom-nav .nav-item.active {
            color: #22c55e;
        }

        .mobile-bottom-nav .nav-item:hover {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.05);
        }

        /* ========== MENU GRID ========== */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 16px;
            background: white;
            margin: 12px 16px;
            border-radius: 16px;
        }

        .menu-grid .menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .menu-grid .menu-item:hover {
            transform: translateY(-4px);
        }

        .menu-grid .menu-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto 8px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .menu-grid .menu-item:hover .menu-icon {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }

        .menu-grid i {
            font-size: 24px;
            color: #22c55e;
            transition: all 0.3s ease;
        }

        .menu-grid .menu-item:hover i {
            color: white;
        }

        .menu-grid small {
            display: block;
            font-size: 12px;
            color: #666;
            text-align: center;
            font-weight: 500;
        }

        /* ========== CARD STYLES ========== */
        .card-section {
            background: white;
            margin: 12px 16px;
            border-radius: 16px;
            padding: 16px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-link {
            font-size: 13px;
            color: #22c55e;
            text-decoration: none;
            font-weight: 500;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 480px) {
            body {
                padding: 0;
                background: #f8f8f8;
            }

            .mobile-view {
                border-radius: 0;
                height: 100vh;
                max-width: 100%;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="mobile-view">
        @yield('navbar')

        <div class="mobile-content">
            @if(session('success'))
            <div class="alert alert-success m-3 py-2 px-3 rounded-3 d-flex align-items-center" style="font-size: 13px;">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger m-3 py-2 px-3 rounded-3 d-flex align-items-center" style="font-size: 13px;">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </div>

        @yield('navbot')
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('gagal'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('gagal') }}",
            confirmButtonColor: '#22c55e',
            confirmButtonText: 'OK'
        })
    </script>
    @endif

    @if(session('sukses'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('sukses') }}",
            confirmButtonColor: '#22c55e'
        })
    </script>
    @endif

    @stack('scripts')
    <script src="{{ asset('js/kurir-notification.js') }}?v={{ time() }}"></script>
</body>

</html>