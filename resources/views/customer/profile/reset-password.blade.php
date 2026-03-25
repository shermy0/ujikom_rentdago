@extends('frontend.master')
@section('navbot')
    @include('frontend.navbot')
@endsection

@section('content')
<style>
    .edit-profile-container {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding-bottom: 80px;
    }
    .edit-header {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        padding: 20px;
        position: relative;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
        box-shadow: 0 4px 20px rgba(238, 77, 45, 0.2);
        padding-bottom: 60px;
    }
    .edit-header-top {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
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
        backdrop-filter: blur(10px);
    }
    .header-back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
    }
    .header-title-text {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        margin-left: 15px;
        letter-spacing: 0.5px;
    }
    .form-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        margin: -40px 15px 20px 15px;
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .form-label-custom {
        font-size: 13px;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control-custom {
        background: #f8f9fa;
        border: 2px solid #edf2f7;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 15px;
        color: #2d3748;
        transition: all 0.3s;
        width: 100%;
        box-sizing: border-box;
    }
    .form-control-custom:focus {
        background: #fff;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255,107,53,0.1);
        outline: none;
    }
    .input-group-custom {
        display: flex;
        align-items: stretch;
        position: relative;
    }
    .input-group-custom .form-control-custom {
        border-bottom-right-radius: 0;
        border-top-right-radius: 0;
        border-right: none;
    }
    .input-group-custom .btn-toggle-pw {
        background: #f8f9fa;
        border: 2px solid #edf2f7;
        border-left: none;
        border-radius: 0 12px 12px 0;
        padding: 0 16px;
        color: #a0aec0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .input-group-custom .form-control-custom:focus + .btn-toggle-pw {
        background: #fff;
        border-color: #ff6b35;
        border-left: none;
        box-shadow: 3px 0 0 3px rgba(255,107,53,0.1) inset;
    }
    .info-box {
        background: #ebf8ff;
        border-left: 4px solid #3182ce;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .info-box ul {
        margin: 0;
        padding-left: 20px;
        color: #2b6cb0;
        font-size: 13px;
        margin-top: 8px;
    }
    .forgot-pw-link {
        font-size: 13px;
        color: #3182ce;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        transition: color 0.2s;
    }
    .forgot-pw-link:hover {
        color: #2b6cb0;
        text-decoration: underline;
    }
    .save-wrapper {
        padding: 0 15px;
    }
    .btn-save {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        color: #fff;
        border: none;
        width: 100%;
        padding: 15px;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(255,107,53,0.3);
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255,107,53,0.4);
    }
    .btn-cancel {
        background: transparent;
        color: #718096;
        border: 2px solid #e2e8f0;
        width: 100%;
        padding: 15px;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        margin-top: 12px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-cancel:hover {
        background: #f7fafc;
        color: #4a5568;
        border-color: #cbd5e0;
    }
</style>

<div class="edit-profile-container">
    <div class="edit-header">
        <div class="edit-header-top">
            <a href="{{ route('profile.edit') }}" class="header-back-btn">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div class="header-title-text">Reset Password</div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 shadow-sm" role="alert" style="border-radius: 12px; border: none; z-index: 10; position: relative;">
        <i class="fa fa-check-circle me-2"></i>{{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm" role="alert" style="border-radius: 12px; border: none; z-index: 10; position: relative;">
        <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('profile.reset.password') }}" method="POST" id="resetPasswordForm">
        @csrf
        @method('PUT')
        
        <div class="form-card">
            <!-- Info Section -->
            <div class="info-box">
                <div class="d-flex align-items-start">
                    <i class="fa fa-info-circle me-2 mt-1" style="color: #3182ce;"></i>
                    <div style="color: #2b6cb0; font-size: 13px; font-weight: 600;">
                        Keamanan Akun
                    </div>
                </div>
                <ul>
                    <li>Minimal 8 karakter</li>
                    <li>Berbeda dari password lama</li>
                    <li>Kombinasi huruf dan angka lebih aman</li>
                </ul>
            </div>

            <!-- Password Lama -->
            <div class="mb-4">
                <label for="current_password" class="form-label-custom">Password Lama <span class="text-danger">*</span></label>
                <div class="input-group-custom">
                    <input type="password" 
                           class="form-control-custom @error('current_password') is-invalid @enderror" 
                           id="current_password" 
                           name="current_password"
                           placeholder="Masukkan password lama"
                           required>
                    <button class="btn-toggle-pw" type="button" onclick="togglePassword('current_password')">
                        <i class="fa fa-eye" id="current_password-icon"></i>
                    </button>
                </div>
                @error('current_password')
                    <div class="text-danger small mt-1"><i class="fa fa-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
                <a href="#" id="forgot-password-link" class="forgot-pw-link">
                    <i class="fa fa-question-circle"></i> Lupa password lama? Verifikasi OTP
                </a>
            </div>

            <hr style="border-color: #e2e8f0; margin: 24px 0;">

            <!-- Password Baru -->
            <div class="mb-4">
                <label for="new_password" class="form-label-custom">Password Baru <span class="text-danger">*</span></label>
                <div class="input-group-custom">
                    <input type="password" 
                           class="form-control-custom @error('new_password') is-invalid @enderror" 
                           id="new_password" 
                           name="new_password"
                           placeholder="Min. 8 karakter"
                           required
                           minlength="8">
                    <button class="btn-toggle-pw" type="button" onclick="togglePassword('new_password')">
                        <i class="fa fa-eye" id="new_password-icon"></i>
                    </button>
                </div>
                @error('new_password')
                    <div class="text-danger small mt-1"><i class="fa fa-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
                
                <div class="mt-2">
                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: #edf2f7;">
                        <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%; border-radius: 3px;"></div>
                    </div>
                    <small id="password-strength-text" class="text-muted d-block mt-1" style="font-size: 11px;"></small>
                </div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="mb-2">
                <label for="new_password_confirmation" class="form-label-custom">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                <div class="input-group-custom">
                    <input type="password" 
                           class="form-control-custom" 
                           id="new_password_confirmation" 
                           name="new_password_confirmation"
                           placeholder="Masukkan ulang password baru"
                           required
                           minlength="8">
                    <button class="btn-toggle-pw" type="button" onclick="togglePassword('new_password_confirmation')">
                        <i class="fa fa-eye" id="new_password_confirmation-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="save-wrapper mb-5">
            <button type="submit" class="btn-save">
                <i class="fa fa-key"></i> Ubah Password
            </button>
            <a href="{{ route('profile.edit') }}" class="btn-cancel">
                <i class="fa fa-times"></i> Batal
            </a>
        </div>
    </form>
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