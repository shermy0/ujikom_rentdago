@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection
@section('navbot')
    @include('kurir.layouts.navbot')
@endsection
@section('content')
<div style="background: #f8f9fa; min-height: 100vh; padding: 20px;">
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
            <!-- Info Alert -->
            <div class="alert alert-info" style="border-radius: 10px; border: none; background: #dcfce7; color: #166534; margin-bottom: 25px;">
                <i class="fa fa-info-circle"></i> Gunakan password yang kuat dan mudah diingat. Password minimal 6 karakter.
            </div>

            <form action="{{ route('kurir.profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Password Lama -->
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                        <i class="fa fa-lock" style="color: #22c55e; margin-right: 5px;"></i>Password Lama
                    </label>
                    <div class="input-group">
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               required
                               style="border-radius: 10px 0 0 10px; padding: 12px 15px; border: 1px solid #e5e7eb;">
                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                data-target="current_password"
                                style="border-radius: 0 10px 10px 0; border-left: none;">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Baru -->
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                        <i class="fa fa-key" style="color: #22c55e; margin-right: 5px;"></i>Password Baru
                    </label>
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password"
                               class="form-control @error('new_password') is-invalid @enderror"
                               required minlength="6"
                               style="border-radius: 10px 0 0 10px; padding: 12px 15px; border: 1px solid #e5e7eb;">
                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                data-target="new_password"
                                style="border-radius: 0 10px 10px 0; border-left: none;">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    @error('new_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                        <i class="fa fa-check-circle" style="color: #22c55e; margin-right: 5px;"></i>Konfirmasi Password Baru
                    </label>
                    <div class="input-group">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                               class="form-control"
                               required minlength="6"
                               style="border-radius: 10px 0 0 10px; padding: 12px 15px; border: 1px solid #e5e7eb;">
                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                data-target="new_password_confirmation"
                                style="border-radius: 0 10px 10px 0; border-left: none;">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Masukkan password baru sekali lagi</small>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; padding: 14px; border-radius: 10px; font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.3s;">
                        <i class="fa fa-save" style="margin-right: 8px;"></i>Ubah Password
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
    .mobile-bottom-nav .nav-item.active,
    .mobile-bottom-nav .nav-item:hover {
        color: #22c55e !important;
    }

    .toggle-password {
        cursor: pointer;
    }
</style>

<script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>
@endsection
