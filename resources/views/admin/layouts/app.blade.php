<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
    $appName = \App\Models\Setting::first()?->app_name ?? 'Customer';
@endphp

<title>{{ $title ?? 'Admin' }} - {{ $appName }}</title>

    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Admin: jarak & komponen responsif (setelah Bootstrap) -->
    <link rel="stylesheet" href="{{ asset('css/admin-responsive.css') }}?v=1">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --primary-color: #ee4d2d;
            --sidebar-bg: #fff;
            --topbar-bg: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        /* Wrapper */
        .wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            opacity: 0;
            visibility: hidden;
            z-index: 998;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), #ff6b35);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            margin-bottom: 5px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-link:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        .menu-link.active {
            background-color: #fff5f2;
            color: var(--primary-color);
            font-weight: 500;
        }

        .menu-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary-color);
        }

        .menu-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            font-size: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            max-width: 100vw;
            overflow-x: hidden;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: var(--topbar-bg);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 clamp(12px, 2.2vw, 30px);
        }

        .btn-sidebar-toggle {
            display: none;
            width: 38px;
            height: 38px;
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 8px;
            color: #333;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
        }

        .topbar-left h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-topbar {
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-profile {
            background-color: #f0f0f0;
            color: #333;
        }

        .btn-profile:hover {
            background-color: #e0e0e0;
        }

        .btn-logout {
            background-color: #dc3545;
            color: white;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }

        /* Content Wrapper — padding menyesuaikan lebar layar */
        .content-wrapper {
            flex: 1;
            padding: clamp(12px, 2.2vw, 30px);
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-item a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }

        .breadcrumb-item.active {
            color: #333;
            font-weight: 500;
        }

        /* Alert */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #f0f0f0;
            padding: clamp(12px, 2vw, 20px) clamp(12px, 2.2vw, 30px);
            margin-top: auto;
        }

        .footer .text-muted {
            margin: 0;
            font-size: 14px;
            color: #999;
        }

        /* Responsif: tablet & mobile (Bootstrap lg ke bawah) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 clamp(10px, 2vw, 16px);
            }

            .topbar-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .btn-sidebar-toggle {
                display: inline-flex;
                flex-shrink: 0;
            }

            .topbar-left h5 {
                font-size: clamp(0.95rem, 2.8vw, 1.1rem);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: min(52vw, 320px);
            }

            .topbar-right {
                gap: 8px;
            }

            .btn-topbar {
                padding: 8px 10px;
                font-size: 13px;
            }

            .btn-topbar span {
                display: none;
            }

            .content-wrapper {
                padding: clamp(10px, 2.5vw, 20px);
            }

            .content-wrapper .card-header,
            .content-wrapper .card-body {
                padding: clamp(10px, 2vw, 16px);
            }

            .content-wrapper .card-header {
                gap: 10px;
                flex-wrap: wrap;
            }

            .content-wrapper .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-x;
            }

            .content-wrapper .table-responsive > .table {
                min-width: 760px;
            }

            .content-wrapper .table-borderless td,
            .content-wrapper .table-borderless th {
                white-space: normal;
                word-break: break-word;
            }

            .menu-link {
                min-height: 44px;
                align-items: center;
            }
        }

        /* HP kecil: tabel scroll horizontal tetap nyaman */
        @media (max-width: 575.98px) {
            .content-wrapper .table-responsive > .table {
                min-width: 640px;
            }
        }

        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            @include('admin.partials.topbar')

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    @if(isset($breadcrumbs))
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @foreach($breadcrumbs as $breadcrumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                    @endif

                    <!-- Alert Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Main Content -->
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <p class="text-muted">© 2025 Rental Store. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        const closeSidebar = () => {
            sidebar?.classList.remove('show');
            sidebarOverlay?.classList.remove('show');
            document.body.style.overflow = '';
        };

        const openSidebar = () => {
            sidebar?.classList.add('show');
            sidebarOverlay?.classList.add('show');
            document.body.style.overflow = 'hidden';
        };

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                const isOpen = sidebar.classList.contains('show');
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        sidebarOverlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                closeSidebar();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>