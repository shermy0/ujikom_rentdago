@extends('frontend.master')
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('navbar')
<div class="mobile-top-header">
    <div class="header-left" style="flex: none; background: transparent; padding: 0;">
        <a href="{{ route('profile.edit') }}" style="color: #fff; font-size: 20px;">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div style="flex: 1; text-align: center;">
        <span style="color: #fff; font-size: 18px; font-weight: 500;">Reset Password</span>
    </div>
    <div class="header-right"></div>
</div>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Alert Messages -->
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Info Section -->
    <div class="bg-light p-3 mx-3 mt-3 rounded">
        <div class="d-flex align-items-start">
            <i class="fa fa-info-circle text-primary me-2 mt-1"></i>
            <div>
                <small class="text-muted">
                    Untuk keamanan akun Anda, pastikan password baru:
                </small>
                <ul class="mb-0 mt-2" style="font-size: 13px;">
                    <li>Minimal 8 karakter</li>
                    <li>Berbeda dari password lama</li>
                    <li>Kombinasi huruf dan angka lebih aman</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="p-3">
        <form action="{{ route('profile.reset.password') }}" method="POST" id="resetPasswordForm">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Password Lama -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">
                            Password Lama <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password"
                                   placeholder="Masukkan password lama"
                                   required>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('current_password')">
                                <i class="fa fa-eye" id="current_password-icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-danger small mt-1">
                                <i class="fa fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        
                        <!-- Link Lupa Password -->
                        <div class="mt-2">
                            <a href="#" id="forgot-password-link" class="small text-primary">
                                <i class="fa fa-question-circle me-1"></i>Lupa password lama? Verifikasi dengan OTP
                            </a>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">
                            Password Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" 
                                   name="new_password"
                                   placeholder="Masukkan password baru (min. 8 karakter)"
                                   required
                                   minlength="8">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('new_password')">
                                <i class="fa fa-eye" id="new_password-icon"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <div class="text-danger small mt-1">
                                <i class="fa fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="password-strength-text" class="text-muted"></small>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="mb-0">
                        <label for="new_password_confirmation" class="form-label fw-bold">
                            Konfirmasi Password Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="new_password_confirmation" 
                                   name="new_password_confirmation"
                                   placeholder="Masukkan ulang password baru"
                                   required
                                   minlength="8">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('new_password_confirmation')">
                                <i class="fa fa-eye" id="new_password_confirmation-icon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Masukkan password yang sama dengan di atas</small>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-3">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fa fa-key me-2"></i>Ubah Password
                </button>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary w-100 mb-5">
                    <i class="fa fa-times me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new_password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    let strength = 0;
    let text = '';
    let color = '';
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]+/)) strength += 25;
    if (password.match(/[A-Z]+/)) strength += 25;
    if (password.match(/[0-9]+/)) strength += 15;
    if (password.match(/[$@#&!]+/)) strength += 10;
    
    if (strength < 40) {
        text = 'Lemah';
        color = 'bg-danger';
    } else if (strength < 60) {
        text = 'Sedang';
        color = 'bg-warning';
    } else if (strength < 80) {
        text = 'Kuat';
        color = 'bg-info';
    } else {
        text = 'Sangat Kuat';
        color = 'bg-success';
    }
    
    strengthBar.style.width = strength + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = password.length > 0 ? 'Kekuatan Password: ' + text : '';
});

// Handle forgot password link
document.getElementById('forgot-password-link').addEventListener('click', function(e) {
    e.preventDefault();
    
    Swal.fire({
        icon: 'question',
        title: 'Lupa Password Lama?',
        html: 'Anda akan menerima kode OTP melalui WhatsApp<br>ke nomor <strong>{{ $user->phone }}</strong><br><br>Lanjutkan?',
        showCancelButton: true,
        confirmButtonColor: '#ff5722',
        cancelButtonColor: '#999',
        confirmButtonText: '<i class="fa fa-paper-plane me-2"></i>Ya, Kirim OTP',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Mengirim OTP...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Request OTP
            fetch('{{ route("profile.request.password.reset.otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Terkirim!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Redirect ke halaman verifikasi OTP
                        window.location.href = data.redirect;
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Terjadi kesalahan saat mengirim OTP'
                });
            });
        }
    });
});

// Form validation
document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password Tidak Cocok',
            text: 'Password baru dan konfirmasi password tidak cocok!'
        });
        return false;
    }
    
    if (newPassword.length < 8) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password Terlalu Pendek',
            text: 'Password baru minimal 8 karakter!'
        });
        return false;
    }
    
    // Konfirmasi sebelum submit
    e.preventDefault();
    Swal.fire({
        icon: 'warning',
        title: 'Konfirmasi',
        text: 'Setelah password diubah, Anda akan logout otomatis. Lanjutkan?',
        showCancelButton: true,
        confirmButtonColor: '#ff5722',
        cancelButtonColor: '#999',
        confirmButtonText: 'Ya, Ubah Password',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>
@endpush
@endsection