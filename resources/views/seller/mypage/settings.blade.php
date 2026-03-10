@extends('frontend.masterseller')

@section('content')
<style>
    /* CSS khusus untuk halaman Settings */
    .settings-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }
    
    /* Header dengan Back Button */
    .settings-header-bar {
        background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .settings-header-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .settings-header-back a {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
    }
    
    .settings-header-title {
        flex: 1;
        text-align: center;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
    }
    
    .settings-header-spacer {
        width: 40px;
    }
    
    .settings-profile {
        background: #fff;
        padding: 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .settings-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ff5722;
        flex-shrink: 0;
    }
    
    .settings-avatar-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ff5722;
        flex-shrink: 0;
    }
    
    .settings-info h6 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 700;
        color: #333;
    }
    
    .settings-info p {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .settings-role-badge {
        background: #28a745;
        color: #fff;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        display: inline-block;
    }
    
    .settings-section {
        padding: 0 1rem;
    }
    
    .settings-card {
        background: #fff;
        border-radius: 12px;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .settings-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .settings-menu-item {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .settings-menu-item:last-child {
        border-bottom: none;
    }
    
    .settings-menu-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        text-decoration: none;
        color: #333;
        transition: background 0.2s;
    }
    
    .settings-menu-link:hover {
        background: #f8f9fa;
    }
    
    .settings-menu-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .settings-menu-icon {
        font-size: 1.125rem;
        width: 24px;
        text-align: center;
    }
    
    .settings-menu-icon.text-primary { color: #007bff; }
    .settings-menu-icon.text-success { color: #28a745; }
    
    .settings-menu-text {
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .settings-menu-arrow {
        color: #999;
        font-size: 0.875rem;
    }
    
    .settings-status-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .settings-status-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #333;
    }
    
    .shop-status-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .shop-mini-logo {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }
    
    .shop-mini-logo-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .shop-status-details p {
        margin: 0 0 0.25rem 0;
        font-weight: 600;
        color: #333;
    }
    
    .shop-status-details small {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .verification-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .verification-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .verification-label p {
        margin: 0 0 0.25rem 0;
        font-weight: 500;
        color: #333;
    }
    
    .verification-label small {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .verification-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .verification-badge.success {
        background: #28a745;
        color: #fff;
    }
    
    .verification-badge.warning {
        background: #ffc107;
        color: #333;
    }
    
   .account-actions {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* base button */
.btn {
    width: 100%;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;

    cursor: pointer;
    background: #fff;
    transition: all 0.2s ease;
}

/* logout (destructive) */
.btn-logout {
    border: 2px solid #ff4d4f;
    color: #ff4d4f;
}

.btn-logout:hover {
    background: rgba(255, 77, 79, 0.08);
}

/* switch account (secondary) */
.btn-switch {
    border: 1.5px solid #ddd;
    color: #333;
}

.btn-switch:hover {
    background: #f5f5f5;
}

</style>

<div class="settings-container">
    
    <!-- Header dengan Back Button -->
    <div class="settings-header-bar">
        <div class="settings-header-back">
            <a href="{{ route('seller.mypage.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="settings-header-title">
            Pengaturan
        </div>
        <div class="settings-header-spacer"></div>
    </div>
<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
    <i class="fa fa-check-circle"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
    <i class="fa fa-exclamation-circle"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

    <!-- Profile Summary -->
    <div class="settings-profile">
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" 
                 alt="Avatar" 
                 class="settings-avatar">
        @else
            <div class="settings-avatar-placeholder">
                <i class="fa fa-user" style="font-size: 30px; color: #fff;"></i>
            </div>
        @endif
        
        <div class="settings-info">
            <h6>{{ $user->name }}</h6>
            <p>{{ $user->phone }}</p>
            <span class="settings-role-badge">{{ ucfirst($user->role) }}</span>
        </div>
    </div>

    <!-- Menu Settings -->
    <div class="settings-section">
        <div class="settings-card">
            <ul class="settings-menu">
                <li class="settings-menu-item">
                    <a href="{{ route('seller.mypage.edit-account') }}" class="settings-menu-link">
                        <div class="settings-menu-left">
                            <i class="fa fa-user-edit settings-menu-icon text-primary"></i>
                            <span class="settings-menu-text">Edit Akun</span>
                        </div>
                        <i class="fa fa-chevron-right settings-menu-arrow"></i>
                    </a>
                </li>

                @if($shop)
                <li class="settings-menu-item">
                    <a href="{{ route('seller.mypage.edit-shop') }}" class="settings-menu-link">
                        <div class="settings-menu-left">
                            <i class="fa fa-store settings-menu-icon text-success"></i>
                            <span class="settings-menu-text">Edit Data Toko</span>
                        </div>
                        <i class="fa fa-chevron-right settings-menu-arrow"></i>
                    </a>
                </li>
                @endif
                @if($shop)
<li class="settings-menu-item">
    <a href="{{ route('seller.couriers.index') }}" class="settings-menu-link">
        <div class="settings-menu-left">
            <i class="fa fa-shipping-fast settings-menu-icon" style="color: #ff6b35;"></i>
            <span class="settings-menu-text">Kelola Kurir</span>
        </div>
        <i class="fa fa-chevron-right settings-menu-arrow"></i>
    </a>
</li>
@endif
            </ul>
        </div>

@if($shop)
<div class="settings-status-card">
    <div class="settings-status-title">Status Toko</div>
    
    <div class="shop-status-info">
        @if($shop->logo)
            <img src="{{ asset('storage/' . $shop->logo) }}" 
                 alt="Logo" 
                 class="shop-mini-logo">
        @else
            <div class="shop-mini-logo-placeholder">
                <i class="fa fa-store" style="color: #6c757d;"></i>
            </div>
        @endif
        
        <div class="shop-status-details" style="flex: 1;">
            <p>{{ $shop->name_store }}</p>
            <small>
                <i class="fa fa-circle" style="color: {{ $shop->is_active ? '#28a745' : '#dc3545' }};"></i>
                {{ $shop->is_active ? 'Toko Aktif' : 'Toko Nonaktif' }}
                
                {{-- Tampilkan badge jika dinonaktifkan oleh admin --}}
                @if(!$shop->is_active && $shop->deactivated_by === 'admin')
                    <span style="
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        background: #fff3cd;
                        color: #856404;
                        padding: 2px 8px;
                        border-radius: 10px;
                        font-size: 11px;
                        margin-left: 6px;
                        border: 1px solid #ffc107;
                    ">
                        <i class="fa fa-lock"></i> Oleh Admin
                    </span>
                @endif
            </small>
        </div>
    </div>

    {{-- Tombol Toggle atau Pesan Locked --}}
    @if(!$shop->is_active && $shop->deactivated_by === 'admin')
        {{-- Jika dinonaktifkan oleh admin, tampilkan tombol disabled --}}
        <button type="button" 
                class="btn-toggle-shop locked"
                onclick="showAdminLockAlert()"
                style="margin-top: 1rem;">
            <i class="fa fa-lock"></i>
            <span>Toko Dikunci Admin</span>
        </button>
        
        <div class="shop-admin-locked-notice">
            <i class="fa fa-shield-alt"></i>
            <div>
                <strong>Toko dinonaktifkan oleh Admin</strong>
                <p>Anda tidak dapat mengaktifkan toko sendiri. Silakan hubungi admin untuk mengaktifkan kembali toko Anda.</p>
            </div>
        </div>
    @else
        {{-- Toggle Button Normal --}}
        <form action="{{ route('seller.mypage.toggle-shop-status') }}" 
              method="POST" 
              style="margin-top: 1rem;"
              onsubmit="return confirmToggle(event, {{ $shop->is_active ? 'true' : 'false' }})">
            @csrf
            <button type="submit" 
                    class="btn-toggle-shop {{ $shop->is_active ? 'active' : 'inactive' }}">
                <i class="fa {{ $shop->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                <span>{{ $shop->is_active ? 'Nonaktifkan Toko' : 'Aktifkan Toko' }}</span>
            </button>
        </form>

        @if(!$shop->is_active)
        <div class="shop-inactive-notice">
            <i class="fa fa-info-circle"></i>
            <span>Toko Anda sedang nonaktif. Pembeli tidak dapat melihat toko dan produk Anda.</span>
        </div>
        @endif
    @endif
</div>
@endif



        <!-- Verification Status -->
        <div class="settings-status-card">
            <div class="settings-status-title">Status Verifikasi</div>
            
            <div class="verification-item">
                <div class="verification-label">
                    <p>Verifikasi Nomor HP</p>
                    <small>Status verifikasi OTP</small>
                </div>
                @if($user->phone_verified_at)
                    <span class="verification-badge success">
                        <i class="fa fa-check-circle"></i> Terverifikasi
                    </span>
                @else
                    <span class="verification-badge warning">
                        <i class="fa fa-clock"></i> Belum Verifikasi
                    </span>
                @endif
            </div>

            <div class="verification-item">
                <div class="verification-label">
                    <p>Verifikasi Akun</p>
                    <small>Persetujuan dari admin</small>
                </div>
                @if($user->user_verified_at)
                    <span class="verification-badge success">
                        <i class="fa fa-check-circle"></i> Disetujui
                    </span>
                @else
                    <span class="verification-badge warning">
                        <i class="fa fa-clock"></i> Menunggu
                    </span>
                @endif
            </div>
        </div>
    </div>

<div class="account-actions">

    <form action="{{ route('auth.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-logout"
            onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="fa fa-sign-out-alt"></i>
            <span>Keluar</span>
        </button>
    </form>

    <form method="POST" action="{{ route('account.switch') }}">
        @csrf
        <button type="submit" class="btn btn-switch">
            <i class="fas fa-repeat"></i>
            <span>Beralih Ke Customer</span>
        </button>
    </form>

</div>

</div>

{{-- JavaScript untuk konfirmasi dan alert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmToggle(event, isActive) {
    event.preventDefault();
    const form = event.target;
    const action = isActive ? 'nonaktifkan' : 'aktifkan';
    
    Swal.fire({
        title: isActive ? 'Nonaktifkan Toko?' : 'Aktifkan Toko?',
        html: isActive 
            ? '<p style="margin: 0; color: #666;">Apakah Anda yakin ingin menonaktifkan toko?</p><p style="margin-top: 8px; color: #e74c3c; font-size: 14px;"><i class="fa fa-exclamation-triangle"></i> Pembeli tidak akan bisa melihat toko dan produk Anda.</p>'
            : '<p style="margin: 0; color: #666;">Apakah Anda yakin ingin mengaktifkan toko kembali?</p>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Ya, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        cancelButtonText: 'Batal',
        confirmButtonColor: isActive ? '#dc3545' : '#28a745',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    
    return false;
}

function showAdminLockAlert() {
    Swal.fire({
        icon: 'warning',
        title: 'Toko Dikunci Admin',
        html: `
            <p style="margin: 0; color: #666; font-size: 15px;">
                Toko Anda dinonaktifkan oleh administrator.
            </p>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                Anda tidak dapat mengaktifkan toko sendiri. Silakan hubungi admin untuk mengaktifkan kembali.
            </p>
        `,
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#ff6b35'
    });
}
</script>



<style>
/* Toggle Button Styles */
.btn-toggle-shop {
    width: 100%;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-toggle-shop.active {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-toggle-shop.active:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
    transform: translateY(-2px);
}

.btn-toggle-shop.inactive {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-toggle-shop.inactive:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
    transform: translateY(-2px);
}

.btn-toggle-shop.locked {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    cursor: pointer;
}

.btn-toggle-shop.locked:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
    transform: translateY(-2px);
}

.btn-toggle-shop i {
    font-size: 20px;
}

/* Inactive Notice */
.shop-inactive-notice {
    margin-top: 1rem;
    padding: 12px 16px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.875rem;
    color: #856404;
}

.shop-inactive-notice i {
    font-size: 18px;
    flex-shrink: 0;
}

/* Admin Locked Notice */
.shop-admin-locked-notice {
    margin-top: 1rem;
    padding: 16px;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.875rem;
    color: #856404;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);
}

.shop-admin-locked-notice i {
    font-size: 24px;
    flex-shrink: 0;
    margin-top: 2px;
}

.shop-admin-locked-notice strong {
    display: block;
    margin-bottom: 4px;
    color: #333;
    font-size: 15px;
}

.shop-admin-locked-notice p {
    margin: 0;
    line-height: 1.5;
}
</style>
@endsection