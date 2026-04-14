@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-person me-2"></i>Detail User
        </h5>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Avatar Section -->
            <div class="col-md-4 text-center mb-4">
                <div class="avatar-wrapper mb-3">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}"
                             alt="{{ $user->name }}"
                             class="rounded-circle shadow"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white shadow"
                             style="width: 150px; height: 150px; font-size: 48px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->phone }}</p>
                @switch($user->role)
                    @case('admin')
                        <span class="badge bg-danger fs-6">Admin</span>
                        @break
                    @case('seller')
                        <span class="badge bg-primary fs-6">Seller</span>
                        @break
                    @case('customer')
                        <span class="badge bg-success fs-6">Customer</span>
                        @break
                    @case('courier')
                        <span class="badge bg-warning text-dark fs-6">Kurir</span>
                        @break
                @endswitch
            </div>

            <!-- Info Section -->
            <div class="col-md-8">
                <h6 class="text-muted mb-3">INFORMASI USER</h6>

                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 200px;" class="text-muted">
                                <i class="bi bi-person me-2"></i>Nama Lengkap
                            </td>
                            <td><strong>{{ $user->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">
                                <i class="bi bi-telephone me-2"></i>Nomor Telepon
                            </td>
                            <td><strong>{{ $user->phone }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">
                                <i class="bi bi-shield-check me-2"></i>Role
                            </td>
                            <td><strong>{{ $user->role == 'courier' ? 'Kurir' : ucfirst($user->role) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">
                                <i class="bi bi-phone me-2"></i>Verifikasi
                            </td>
                            <td>
                                @if($user->phone_verified_at)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Terverifikasi pada {{ $user->phone_verified_at->format('d M Y H:i') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Belum Verifikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>

                        </tr>
                        <tr>
                            <td class="text-muted">
                                <i class="bi bi-calendar-plus me-2"></i>Terdaftar Pada
                            </td>
                            <td><strong>{{ $user->created_at->format('d M Y H:i') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">
                                <i class="bi bi-calendar-check me-2"></i>Terakhir Diupdate
                            </td>
                            <td><strong>{{ $user->updated_at->format('d M Y H:i') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    .card-header {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-body {
        padding: 25px;
    }
    .avatar-wrapper {
        padding: 20px;
    }
    .btn-warning {
        background: linear-gradient(135deg, #ffc107, #ffca28);
        border: none;
        color: #333;
    }
    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800, #e0b800);
        color: #333;
    }
</style>
@endpush
@endsection
