@extends('frontend.master')


  @php
    $appName = \App\Models\Setting::first()?->app_name ?? 'Login';
@endphp
<title>{{ $title ?? 'Login' }} - {{ $appName }}</title>
@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbar')
    <div class="mobile-top-header">
        <a href="{{ route('home') }}" style="color: #fff; font-size: 20px; margin-right: 15px;">
            <i class="fa fa-arrow-left"></i>
        </a>
        <span style="color: #fff; font-size: 16px; font-weight: 500;">Kembali</span>
    </div>
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<div class="container py-4" style="background: #fff; min-height: calc(100vh - 115px);">

    <!-- TITLE -->
    <div class="text-center mb-4">
        <h4 class="font-weight-bold" style="color: #333;">Selamat Datang di {{ $appName }}</h4>
        <p class="text-muted small">
            Login menggunakan nomor WhatsApp
        </p>
    </div>

    <!-- ALERT SUCCESS -->
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
        <i class="fa fa-check-circle me-2"></i>{{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- ALERT ERROR -->
    <div id="error-alert" class="alert alert-danger" style="display: none; border-radius: 10px;">
        <small id="error-message"></small>
    </div>

    <!-- LOGIN FORM -->
    <form id="login-form" action="{{ route('auth.login.post') }}" method="POST">
        @csrf

        <!-- PHONE -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">Nomor WhatsApp</label>
            <input type="text"
                   name="phone"
                   id="phone"
                   class="form-control rounded-pill px-4"
                   style="border: 1px solid #ddd; height: 45px;"
                   placeholder="Contoh: 08123456789"
                   required
                   autofocus>
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
                       placeholder="Masukkan password"
                       required>
                <i class="fa fa-eye" 
                   id="toggle-password" 
                   style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;"></i>
            </div>
        </div>

        <!-- FORGOT PASSWORD - UPDATE INI -->
        <div class="text-right mb-3">
            <a href="{{ route('auth.forgot-password') }}" class="small" style="color: #ff5722;">
                <i class="fa fa-question-circle me-1"></i>Lupa Password?
            </a>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                id="login-btn"
                class="btn btn-block text-white rounded-pill mt-3"
                style="background:#ff5722; height: 45px; font-weight: 500;">
            Masuk
        </button>
    </form>

    <!-- REGISTER -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Belum punya akun?
            <a href="{{ route('auth.register') }}"
               class="font-weight-bold"
               style="color:#ff5722">
                Daftar Sekarang
            </a>
        </small>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

// Handle Form Submit dengan AJAX
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('login-btn');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    
    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
    errorAlert.style.display = 'none';
    
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
            throw new Error(data.message || 'Login gagal');
        }
        return data;
    })
    .then(data => {
        if (data.message === 'Login berhasil') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Login berhasil',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect berdasarkan role
                window.location.href = data.redirect;
            });
        } else {
            throw new Error(data.message || 'Login gagal');
        }
    })
    .catch(error => {
        // Show error message
        errorMessage.innerHTML = '<i class="fa fa-exclamation-circle me-2"></i>' + (error.message || 'Nomor atau password salah');
        errorAlert.style.display = 'block';
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = 'Masuk';
        
        // Auto hide alert after 3 seconds
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 3000);
    });
});
</script>
@endpush
@endsection