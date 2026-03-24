@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection


@section('content')
<style>
    /* ========================================
       GLOBAL & CONTAINER
       ======================================== */
    .profile-container {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding: 0;
        margin: 0;
    }
    
    .profile-container * {
        box-sizing: border-box;
    }
    
    /* ========================================
       HEADER - FIXED Z-INDEX
       ======================================== */
    .profile-header {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: 0 2px 20px rgba(238, 77, 45, 0.3);
    }
    
    .profile-header-top {
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
        color: #fff;
    }
    
    .header-title {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
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
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
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
    
    .badge-customer {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: #fff;
    }
    
    .badge-verified {
        background: #48bb78;
        color: #fff;
    }
    
    /* ========================================
       MAIN CONTENT
       ======================================== */
    .profile-content {
        padding: 20px;
        position: relative;
        z-index: 1;
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
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
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
        background: #fff5f2;
        border-color: #ee4d2d;
        transform: translateX(5px);
        color: #2d3748;
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
        color: #ee4d2d;
        transform: translateX(3px);
    }
    
    /* Special styles for specific actions */
    .btn-action.address-action .btn-action-icon {
        color: #ed8936;
    }
    
    .btn-action.voucher-action .btn-action-icon {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        color: #fff;
    }
    
    .btn-action.seller-action .btn-action-icon {
        color: #48bb78;
    }
    
    .btn-action.edit-action .btn-action-icon {
        color: #4facfe;
    }
    
    /* ========================================
       SELLER REQUEST SECTION
       ======================================== */
    .seller-request-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .seller-request-title {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .seller-request-desc {
        font-size: 14px;
        color: #718096;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .seller-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .seller-status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .seller-status-badge.rejected {
        background: #fed7d7;
        color: #742a2a;
    }
    
    .rejection-note {
        background: #fed7d7;
        border: 1px solid #f56565;
        border-radius: 10px;
        padding: 12px;
        margin-top: 10px;
        font-size: 13px;
        color: #742a2a;
    }
    
    .rejection-note strong {
        display: block;
        margin-bottom: 4px;
    }
    
    .verification-notice {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 10px;
        padding: 12px;
        margin-top: 10px;
        font-size: 13px;
        color: #92400e;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    
    .verification-notice i {
        margin-top: 2px;
    }
    
    /* ========================================
       ACCOUNT ACTIONS (LOGOUT)
       ======================================== */
    .account-actions {
        padding: 5px;
        display: grid;
        gap: 0px;
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
    
    .btn-edit-profile {
        background: #fff;
        color: #2d3748;
        border: 2px solid #e2e8f0;
    }
    
    .btn-edit-profile:hover {
        border-color: #ee4d2d;
        background: #fff5f2;
        transform: translateY(-2px);
    }
    
    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 576px) {
        .profile-header {
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

<div class="profile-container">
    
    <!-- Header -->
    <div class="profile-header">
        <div class="profile-header-top">
            <a href="{{ route('home') }}" class="header-back-btn">
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
                    <span class="profile-badge badge-customer">
                        <i class="fa fa-user"></i>
                        Customer
                    </span>
                    @if($user->phone_verified_at)
                        <span class="profile-badge badge-verified">
                            <i class="fa fa-check-circle"></i>
                            Terverifikasi
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="profile-content">
        
        <!-- SECTION: AKUN SAYA -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fa fa-user"></i>
                </div>
                <div class="section-title">Akun Saya</div>
            </div>
            <div class="section-body">
                <!-- Info Grid -->
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-item-icon" style="color: #ee4d2d;">
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
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons" style="margin-top: 20px;">
                    <!-- Alamat Pengiriman -->
                    <a href="{{ route('customer.addresses.index') }}" class="btn-action address-action">
                        <div class="btn-action-left">
                            <div class="btn-action-icon">
                                <i class="fa fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <div class="btn-action-text">Alamat Pengiriman</div>
                                <small style="color: #718096; font-size: 12px;">Kelola alamat pengiriman Anda</small>
                            </div>
                        </div>
                        <i class="fa fa-chevron-right btn-action-arrow"></i>
                    </a>
                    
                    <!-- Voucher Saya -->
                    <a href="{{ route('customer.vouchers.my') }}" class="btn-action voucher-action">
                        <div class="btn-action-left">
                            <div class="btn-action-icon">
                                <i class="fa fa-ticket-alt"></i>
                            </div>
                            <div>
                                <div class="btn-action-text">Voucher Saya</div>
                                <small style="color: #718096; font-size: 12px;">Lihat voucher yang Anda miliki</small>
                            </div>
                        </div>
                        <i class="fa fa-chevron-right btn-action-arrow"></i>
                    </a>
                    
                    <!-- Edit Profil -->
                    <a href="{{ route('profile.edit') }}" class="btn-action edit-action">
                        <div class="btn-action-left">
                            <div class="btn-action-icon">
                                <i class="fa fa-user-edit"></i>
                            </div>
                            <div>
                                <div class="btn-action-text">Edit Profil</div>
                                <small style="color: #718096; font-size: 12px;">Ubah informasi akun Anda</small>
                            </div>
                        </div>
                        <i class="fa fa-chevron-right btn-action-arrow"></i>
                    </a>
                </div>
            </div>
        </div>

<!-- SELLER REQUEST SECTION -->
@auth
<div class="seller-request-card">
    <div class="seller-request-title">
        <i class="fa fa-store" style="color: #f5576c;"></i>
        Jadi Seller
    </div>
    
    @if(Auth::user()->role === 'seller')
        <!-- Sudah Seller -->
        <div class="seller-request-desc">
            Anda sudah terdaftar sebagai seller. Beralih ke akun seller untuk mengelola toko Anda.
        </div>

    @elseif($sellerRequest && $sellerRequest->status === 'pending')
        <!-- Pengajuan Pending -->
        <div class="seller-request-desc">
            Pengajuan Anda untuk menjadi seller sedang ditinjau oleh admin.
        </div>
        <span class="seller-status-badge pending">
            <i class="fa fa-clock"></i>
            Sedang Ditinjau
        </span>
        
    @elseif($sellerRequest && $sellerRequest->status === 'rejected')
        <!-- Pengajuan Ditolak -->
        <div class="seller-request-desc">
            Pengajuan Anda ditolak. Anda dapat mengajukan ulang untuk menjadi seller.
        </div>
        <span class="seller-status-badge rejected">
            <i class="fa fa-times-circle"></i>
            Ditolak
        </span>
        
        @if($sellerRequest->admin_notes)
        <div class="rejection-note">
            <strong>Alasan Penolakan:</strong>
            {{ $sellerRequest->admin_notes }}
        </div>
        @endif
        
        <a href="{{ route('seller-request.create') }}" class="btn-action seller-action" style="margin-top: 15px;">
            <div class="btn-action-left">
                <div class="btn-action-icon">
                    <i class="fa fa-redo"></i>
                </div>
                <span class="btn-action-text">Ajukan Ulang Jadi Seller</span>
            </div>
            <i class="fa fa-chevron-right btn-action-arrow"></i>
        </a>

    @else
        <!-- Belum Ajukan (boleh walau belum verifikasi) -->
        <div class="seller-request-desc">
            Mulai berjualan dan raih pelanggan lebih banyak dengan menjadi seller.
        </div>

        <a href="{{ route('seller-request.create') }}" class="btn-action seller-action" style="margin-top: 10px;">
            <div class="btn-action-left">
                <div class="btn-action-icon">
                    <i class="fa fa-store"></i>
                </div>
                <span class="btn-action-text">Ajukan Jadi Seller</span>
            </div>
            <i class="fa fa-chevron-right btn-action-arrow"></i>
        </a>
    @endif
</div>
@endauth

<!-- Account Actions -->
<div class="account-actions">
    <form id="logoutForm" action="{{ route('auth.logout') }}" method="POST">
        @csrf
        <button type="button" class="btn-account-action btn-logout" onclick="confirmLogout()">
            <i class="fa fa-sign-out-alt"></i>
            <span>Keluar</span>
        </button>
    </form>
</div>

</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to top when page loads
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Anda akan logout dari akun ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f5576c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logoutForm').submit();
        }
    });
}
</script>

@endsection