@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection
@section('navbot')
    @include('kurir.layouts.navbot')
@endsection
@section('content')
<div class="kurir-inner-page">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);">
        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <div class="card-body" style="padding: 25px;">
            <form action="{{ route('kurir.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Profile Picture -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid #dcfce7;">
                        <i class="fa fa-user" style="font-size: 40px; color: #fff;"></i>
                    </div>
                </div>

                <!-- Nama -->
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                        <i class="fa fa-user" style="color: #22c55e; margin-right: 5px;"></i>Nama Lengkap
                    </label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required
                           style="border-radius: 10px; padding: 12px 15px; border: 1px solid #e5e7eb; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'"
                           onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nomor Telepon -->
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                        <i class="fa fa-phone" style="color: #22c55e; margin-right: 5px;"></i>Nomor Telepon
                    </label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $user->phone) }}" required
                           pattern="[0-9]{10,15}"
                           placeholder="Contoh: 081234567890"
                           style="border-radius: 10px; padding: 12px 15px; border: 1px solid #e5e7eb; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34, 197, 94, 0.1)'"
                           onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Masukkan nomor telepon dengan format yang benar (10-15 digit)</small>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; padding: 14px; border-radius: 10px; font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.3s;">
                        <i class="fa fa-save" style="margin-right: 8px;"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('kurir.profile') }}" class="btn btn-outline-secondary"
                       style="padding: 14px; border-radius: 10px; font-weight: 600;">
                        <i class="fa fa-times" style="margin-right: 8px;"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .kurir-inner-page {
        background: #f8f9fa;
        padding: 16px;
        flex: 1;
    }
    @media (min-width: 992px) {
        .kurir-inner-page { padding: 24px; }
    }
    .mobile-bottom-nav .nav-item.active,
    .mobile-bottom-nav .nav-item:hover {
        color: #22c55e !important;
    }
</style>
@endsection
