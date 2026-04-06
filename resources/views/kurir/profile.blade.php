@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection
@section('navbot')
    @include('kurir.layouts.navbot')
@endsection
@section('content')
<div class="pb-5">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="text-center pt-4 px-4 text-white">
            <div class="position-relative d-inline-block mb-3">
                <div class="profile-avatar">
                    <i class="fa fa-user" style="font-size: 40px;"></i>
                </div>
            </div>
            <h5 class="fw-bold mb-1">{{ auth()->user()->name ?? 'Guest Kurir' }}</h5>
            <p class="mb-3 opacity-75 small">{{ auth()->user()->phone ?? '-' }}</p>
        </div>
    </div>


    <div class="px-3" style="margin-top: -30px;">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card-custom p-3 text-center h-100">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.1);">
                        <i class="fa fa-box" style="color: #22c55e;"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalCount }}</h4>
                    <small class="text-muted" style="font-size: 11px;">Total Pengiriman</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card-custom p-3 text-center h-100">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.1);">
                        <i class="fa fa-calendar-check" style="color: #22c55e;"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $monthCount }}</h4>
                    <small class="text-muted" style="font-size: 11px;">Bulan Ini</small>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center" role="alert">
            <i class="fa fa-check-circle me-2 fs-5"></i>
            <div class="small">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center" role="alert">
            <i class="fa fa-exclamation-circle me-2 fs-5"></i>
            <div class="small">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Menu Settings -->
        <h6 class="fw-bold mb-3 px-1 text-secondary" style="font-size: 13px; letter-spacing: 0.5px;">PENGATURAN AKUN</h6>
        
        <div class="card-custom overflow-hidden mb-4">
            <div class="list-group list-group-flush">
                <a href="{{ route('kurir.profile.edit') }}" class="list-group-item list-group-item-action p-3 border-0 border-bottom d-flex align-items-center">
                    <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background: #ecfdf5;">
                        <i class="fa fa-user-edit text-primary-custom"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0" style="font-size: 14px;">Edit Profil</h6>
                        <small class="text-muted" style="font-size: 11px;">Ubah nama dan info kontak</small>
                    </div>
                    <i class="fa fa-chevron-right text-muted" style="font-size: 12px;"></i>
                </a>
                
                <a href="{{ route('kurir.profile.change-password') }}" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center">
                    <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background: #ecfdf5;">
                        <i class="fa fa-lock text-primary-custom"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0" style="font-size: 14px;">Ubah Password</h6>
                        <small class="text-muted" style="font-size: 11px;">Amankan akun anda</small>
                    </div>
                    <i class="fa fa-chevron-right text-muted" style="font-size: 12px;"></i>
                </a>
            </div>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="btn btn-white w-100 py-3 rounded-4 shadow-sm text-danger fw-bold d-flex align-items-center justify-content-center" style="background: white;">
                <i class="fa fa-sign-out-alt me-2"></i> Keluar Aplikasi
            </button>
        </form>
        
        <div class="text-center mt-4 mb-5">
            <small class="text-muted" style="font-size: 10px;">Versi Aplikasi 1.0.0</small>
        </div>
    </div>
</div>

<style>
    .profile-header {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        padding-bottom: 40px;
        border-radius: 0 0 30px 30px;
        margin-top: -1px; /* Remove gap with header */
        margin-bottom: 16px;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid rgba(255,255,255,0.3);
    }

    .card-custom {
        background: white;
        border-radius: 16px;
    }

    .text-primary-custom {
        color: #22c55e !important;
    }

    .bg-primary-custom {
        background-color: #22c55e !important;
    }
</style>
@endsection
