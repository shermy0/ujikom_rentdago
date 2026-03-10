@extends('frontend.master')
  @php
    $appName = \App\Models\Setting::first()?->app_name ?? 'Seller';
@endphp

<title>{{ $title ?? 'Register' }} - {{ $appName }}</title>

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbar')
    <div class="mobile-top-header">
        <a href="{{ route('home') }}" style="color: #fff; font-size: 20px; margin-right: 15px;">
            <i class="fa fa-arrow-left"></i>
        </a>
        <span style="color: #fff; font-size: 16px; font-weight: 500;">Daftar Akun</span>
    </div>
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<div class="container py-4" style="background: #fff; min-height: calc(100vh - 115px);">

    <!-- TITLE -->
    <div class="text-center mb-4">
        <h4 class="font-weight-bold" style="color: #333;">Buat Akun Baru</h4>
        <p class="text-muted small">
            Daftar menggunakan nomor WhatsApp
        </p>
    </div>

    <!-- ALERT ERROR -->
    <div id="error-alert" class="alert alert-danger" style="display: none; border-radius: 10px;">
        <small id="error-message"></small>
    </div>

    <!-- ALERT SUCCESS -->
    <div id="success-alert" class="alert alert-success" style="display: none; border-radius: 10px;">
        <small id="success-message"></small>
    </div>

    <!-- REGISTER FORM -->
    <form id="register-form" action="{{ route('auth.register.post') }}" method="POST">
        @csrf

        <!-- NAME -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">Nama Lengkap</label>
            <input type="text"
                   name="name"
                   id="name"
                   class="form-control rounded-pill px-4"
                   style="border: 1px solid #ddd; height: 45px;"
                   placeholder="Masukkan nama lengkap"
                   required
                   autofocus>
        </div>

        <!-- PHONE -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">Nomor WhatsApp</label>
            <input type="text"
                   name="phone"
                   id="phone"
                   class="form-control rounded-pill px-4"
                   style="border: 1px solid #ddd; height: 45px;"
                   placeholder="Contoh: 08123456789"
                   required>
            <small class="text-muted">Nomor ini akan digunakan untuk login</small>
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">Password</label>
            <div style="position: relative;">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control rounded-pill px-4"
                       style="border: 1px solid #ddd; height: 45px; padding-right: 45px;"
                       placeholder="Minimal 6 karakter"
                       required>
                <i class="fa fa-eye" 
                   id="toggle-password" 
                   style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
            </div>
        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">Konfirmasi Password</label>
            <div style="position: relative;">
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="form-control rounded-pill px-4"
                       style="border: 1px solid #ddd; height: 45px; padding-right: 45px;"
                       placeholder="Ulangi password"
                       required>
                <i class="fa fa-eye" 
                   id="toggle-password-confirm" 
                   style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
            </div>
        </div>

        <!-- TERMS -->
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" 
                       class="custom-control-input" 
                       id="terms"
                       required>
                <label class="custom-control-label small" 
                       for="terms" 
                       style="color: #666;">
                    Saya setuju dengan 
                    <a href="#" style="color: #ff5722;">Syarat & Ketentuan</a>
                </label>
            </div>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                id="register-btn"
                class="btn btn-block text-white rounded-pill mt-3"
                style="background:#ff5722; height: 45px; font-weight: 500;">
            Daftar Sekarang
        </button>
    </form>

    <!-- LOGIN LINK -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}"
               class="font-weight-bold"
               style="color:#ff5722">
                Masuk Sekarang
            </a>
        </small>
    </div>
</div>

@push('scripts')
<script>
// Toggle Show/Hide Password
document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Toggle Show/Hide Confirm Password
document.getElementById('toggle-password-confirm').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirmation');
    const icon = this;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Validasi Password Match
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    
    if (passwordConfirm && password !== passwordConfirm) {
        this.setCustomValidity('Password tidak cocok');
        this.style.borderColor = '#dc3545';
    } else {
        this.setCustomValidity('');
        this.style.borderColor = '#ddd';
    }
});

// Handle Form Submit dengan AJAX
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('register-btn');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    const successAlert = document.getElementById('success-alert');
    const successMessage = document.getElementById('success-message');
    
    // Validasi password match
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    
    if (password !== passwordConfirm) {
        errorMessage.textContent = 'Password dan konfirmasi password tidak cocok';
        errorAlert.style.display = 'block';
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 3000);
        return;
    }
    
    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mendaftar...';
    errorAlert.style.display = 'none';
    successAlert.style.display = 'none';
    
    // Get form data
    const formData = new FormData(this);
    
    // Send AJAX request
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            // Handle validation errors from Laravel (422)
            if (response.status === 422 && data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                throw new Error(firstError);
            }
            throw new Error(data.message || 'Registrasi gagal');
        }
        return data;
    })
    .then(data => {
        if (data.success || (data.message && data.message.includes('berhasil'))) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `
                    <p>${data.message}</p>
                    ${data.otp_demo ? `<p class="mt-2"><strong>Kode OTP Demo:</strong> ${data.otp_demo}</p>` : ''}
                `,
                confirmButtonColor: '#ff5722',
                confirmButtonText: 'Verifikasi Sekarang'
            }).then(() => {
                // Redirect ke halaman verifikasi OTP
                window.location.href = "{{ route('auth.verify.otp') }}?phone=" + encodeURIComponent(formData.get('phone'));
            });
        } else {
            throw new Error(data.message || 'Registrasi gagal');
        }
    })
    .catch(error => {
        // Show error message
        errorMessage.textContent = error.message || 'Terjadi kesalahan, silakan coba lagi';
        errorAlert.style.display = 'block';
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = 'Daftar Sekarang';
        
        // Auto hide alert after 5 seconds
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 5000);
    });
});

// Real-time phone validation
document.getElementById('phone').addEventListener('input', function() {
    // Hanya angka
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Validasi format Indonesia
    if (this.value.length > 0 && !this.value.startsWith('0')) {
        this.style.borderColor = '#dc3545';
    } else {
        this.style.borderColor = '#ddd';
    }
});
</script>
@endpush
@endsection