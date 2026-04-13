@extends('admin.layouts.app')

@section('content')
<div class="profile-container">
    <!-- Profile Card -->
    <div class="card profile-card">
        <div class="card-body">
            @if(session('sukses'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('sukses') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="profile-header">
                <!-- Avatar -->
                <div class="profile-avatar-wrapper">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                             alt="{{ $user->name }}" 
                             class="profile-avatar">
                    @else
                        <div class="profile-avatar-placeholder">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="role-badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                </div>

                <!-- Profile Info -->
                <div class="profile-info">
                    <h2 class="profile-name">{{ $user->name }}</h2>
                    <p class="profile-phone">
                        <i class="bi bi-phone me-2"></i>{{ $user->phone }}
                    </p>
                </div>
            </div>

            <hr class="my-4">

            <!-- Detail Info -->
            <div class="profile-details">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">
                                <i class="bi bi-person-badge me-2"></i>Role
                            </label>
                            <p class="detail-value">
                                @switch($user->role)
                                    @case('admin')
                                        <span class="badge bg-danger">Admin</span>
                                        @break
                                    @case('seller')
                                        <span class="badge bg-primary">Seller</span>
                                        @break
                                    @case('customer')
                                        <span class="badge bg-success">Customer</span>
                                        @break
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">
                                <i class="bi bi-calendar-check me-2"></i>Bergabung Sejak
                            </label>
                            <p class="detail-value">{{ $user->created_at->format('d F Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <label class="detail-label">
                                <i class="bi bi-phone-vibrate me-2"></i>Status Verifikasi HP
                            </label>
                            <p class="detail-value">
                                @if($user->phone_verified_at)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Menunggu
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Action Buttons -->
            <div class="profile-actions">
                <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .profile-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .profile-card .card-body {
        padding: 40px;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .profile-avatar-wrapper {
        position: relative;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .profile-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 600;
        color: white;
        box-shadow: 0 4px 15px rgba(238, 77, 45, 0.3);
    }

    .role-badge {
        position: absolute;
        bottom: 5px;
        right: -5px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-admin {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .badge-seller {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: white;
    }

    .badge-customer {
        background: linear-gradient(135deg, #198754, #157347);
        color: white;
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }

    .profile-phone {
        font-size: 16px;
        color: #666;
        margin: 0;
    }

    .profile-details {
        margin-top: 20px;
    }

    .detail-item {
        margin-bottom: 20px;
    }

    .detail-label {
        display: block;
        font-size: 13px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 16px;
        color: #333;
        margin: 0;
    }

    .profile-actions {
        display: flex;
        gap: 15px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #d94429, #e55a2b);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(238, 77, 45, 0.3);
    }

    @media (max-width: 576px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .profile-card .card-body {
            padding: 25px;
        }

        .profile-name {
            font-size: 24px;
        }

        .profile-actions {
            flex-direction: column;
        }

        .profile-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush
@endsection
