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
        <span style="color: #fff; font-size: 18px; font-weight: 500;">Buat Password Baru</span>
    </div>
    <div class="header-right"></div>
</div>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Success Icon -->
    <div class="text-center mb-4 mt-4">
        <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <i class="fa fa-check-circle" style="font-size: 40px; color: #28a745;"></i>
        </div>
        <h5 class="font-weight-bold" style="color: #333;">Verifikasi Berhasil!</h5>
        <p class="text-muted small">Silakan buat password baru untuk akun Anda</p>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
        <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Info Section -->
    <div class="bg-light p-3 mx-3 rounded">
        <div class="d-flex align-items-start">
            <i class="fa fa-shield-alt text-success me-2 mt-1"></i>
            <div>
                <small class="text-muted">
                    <strong>Tips password yang aman:</strong>
                </small>
                <ul class="mb-0 mt-2" style="font-size: 13px;">
                    <li>Minimal 8 karakter</li>
                    <li>Gunakan kombinasi huruf besar, kecil, dan angka</li>
                    <li>Hindari informasi pribadi (tanggal lahir, nama, dll)</li>
                    <li>Jangan gunakan password yang mudah ditebak</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="p-3">
        <form action="{{ route('profile.update.password.with.otp') }}" method="POST" id="newPasswordForm">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
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
                                   minlength="8"
                                   autofocus>
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
                        
                        <!-- Match indicator -->
                        <div id="match-indicator" class="mt-2" style="display: none;">
                            <small class="text-success">
                                <i class="fa fa-check-circle me-1"></i>Password cocok
                            </small>
                        </div>
                        <div id="nomatch-indicator" class="mt-2" style="display: none;">
                            <small class="text-danger">
                                <i class="fa fa-times-circle me-1"></i>Password tidak cocok
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-3">
                <button type="submit" class="btn btn-success w-100 mb-2" id="submit-btn" disabled>
                    <i class="fa fa-key me-2"></i>Simpan Password Baru
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
    
    // Check match
    checkPasswordMatch();
});

// Check password match
function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    const matchIndicator = document.getElementById('match-indicator');
    const nomatchIndicator = document.getElementById('nomatch-indicator');
    const submitBtn = document.getElementById('submit-btn');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword && password.length >= 8) {
            matchIndicator.style.display = 'block';
            nomatchIndicator.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        } else {
            matchIndicator.style.display = 'none';
            nomatchIndicator.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
        }
    } else {
        matchIndicator.style.display = 'none';
        nomatchIndicator.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
    }
}

document.getElementById('new_password_confirmation').addEventListener('input', checkPasswordMatch);

// Form validation
document.getElementById('newPasswordForm').addEventListener('submit', function(e) {
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
        html: 'Password akan diubah dan Anda akan logout otomatis.<br><br>Pastikan Anda mengingat password baru!<br><br>Lanjutkan?',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#999',
        confirmButtonText: '<i class="fa fa-check me-2"></i>Ya, Simpan Password',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            this.submit();
        }
    });
});
</script>
@endpush
@endsection