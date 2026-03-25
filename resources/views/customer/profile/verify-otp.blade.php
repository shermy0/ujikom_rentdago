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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding-bottom: 70px;
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
        font-size: 18px;
        font-weight: 700;
        margin-left: 15px;
        letter-spacing: 0.5px;
    }
    .form-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        margin: -50px 15px 20px 15px;
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: -60px auto 20px auto;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .icon-wrapper.mode-reset,
    .icon-wrapper.mode-phone {
        background: #fff3f0;
        color: #ff5722;
    }
    .icon-wrapper i {
        font-size: 35px;
    }
    .otp-input {
        width: 45px;
        height: 55px;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
        border: 2px solid #edf2f7;
        border-radius: 12px;
        background: #f8f9fa;
        color: #2d3748;
        transition: all 0.3s;
        padding: 0;
    }
    .otp-input:focus {
        background: #fff;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255,107,53,0.1);
        outline: none;
    }
    .save-wrapper {
        padding: 0 15px;
    }
    .btn-verify {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        color: #fff;
        border: none;
        width: 100%;
        padding: 16px;
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
    .btn-verify:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255,107,53,0.4);
    }
    .btn-verify:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .resend-link {
        font-weight: 700;
        color: #ff5722;
        text-decoration: none;
        transition: opacity 0.3s;
    }
    .resend-link:hover {
        opacity: 0.8;
    }
    .step-indicator {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s;
        z-index: 2;
    }
    .step-line {
        flex: 1;
        height: 3px;
        background: #edf2f7;
        margin: 0 -5px;
        z-index: 1;
        transition: all 0.3s;
    }
</style>

<div class="edit-profile-container">
    <div class="edit-header">
        <div class="edit-header-top">
            <a href="{{ $isPasswordReset ?? false ? route('profile.reset.password') : route('profile.edit') }}" class="header-back-btn">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div class="header-title-text text-truncate">
                {{ $isPasswordReset ?? false ? 'Verifikasi Reset Password' : 'Verifikasi Perubahan Nomor' }}
            </div>
        </div>
    </div>

    <div class="form-card text-center">
        <!-- ICON -->
        <div class="icon-wrapper {{ $isPasswordReset ?? false ? 'mode-reset' : 'mode-phone' }}">
            <i class="fa {{ $isPasswordReset ?? false ? 'fa-lock' : 'fa-shield-alt' }}"></i>
        </div>
        
        <h5 class="fw-bold mb-3" style="color: #2d3748;">
            {{ $isPasswordReset ?? false ? 'Verifikasi Keamanan' : 'Verifikasi Nomor OTP' }}
        </h5>

        @if($isPasswordReset ?? false)
            <p class="text-muted small mb-4" style="font-size: 13px;">
                Kode OTP telah dikirim ke nomor WhatsApp Anda<br>
                <strong id="phone-display" style="color: #2d3748; font-size: 14px;">{{ $phone }}</strong>
            </p>
        @else
            <!-- STEP INDICATOR -->
            <div class="d-flex justify-content-center align-items-center mb-3 px-4">
                <div class="step-indicator" id="step-1" style="background: #ff5722; color: #fff; box-shadow: 0 2px 8px rgba(255,87,34,0.3);">1</div>
                <div class="step-line" id="line-1"></div>
                <div class="step-indicator" id="step-2" style="background: #edf2f7; color: #a0aec0;">2</div>
            </div>

            <p class="text-muted small mb-3" id="step-description" style="font-size: 13px;">
                @if(isset($step) && $step == 2)
                    <strong style="color: #2d3748;">Tahap 2:</strong> Kode OTP telah dikirim ke nomor baru Anda<br>
                    <strong id="phone-display" style="color: #2d3748; font-size: 14px;">{{ $phone }}</strong>
                @else
                    <strong style="color: #2d3748;">Tahap 1:</strong> Kode OTP telah dikirim ke nomor lama Anda<br>
                    <strong id="phone-display" style="color: #2d3748; font-size: 14px;">{{ $phone }}</strong>
                @endif
            </p>
            
            @if(isset($newPhone))
            <div class="alert alert-info py-2 px-3 mb-4" style="border-radius: 10px; font-size: 12px; background: #ebf8ff; border: 1px solid #bee3f8; color: #2b6cb0;">
                <i class="fa fa-info-circle me-1"></i> Nomor akan diubah ke: <strong class="ms-1">{{ $newPhone }}</strong>
            </div>
            @endif
        @endif

        <!-- ALERTS -->
        <div id="error-alert" class="alert alert-danger py-2" style="display: none; border-radius: 10px; border: none; background: #fff5f5; color: #c53030;">
            <small id="error-message" class="fw-bold"><i class="fa fa-exclamation-circle me-1"></i>Error</small>
        </div>

        <div id="success-alert" class="alert alert-success py-2" style="display: none; border-radius: 10px; border: none; background: #f0fff4; color: #2f855a;">
            <small id="success-message" class="fw-bold"><i class="fa fa-check-circle me-1"></i>Success</small>
        </div>

        <!-- OTP FORM -->
        <form id="otp-form" action="{{ route('profile.verify.otp.post') }}" method="POST">
            @csrf
            <input type="hidden" name="phone" id="phone-input" value="{{ $phone }}">
            <input type="hidden" name="step" id="step-input" value="{{ $step ?? 1 }}">

            <div class="mb-4">
                <label class="form-label text-uppercase fw-bold text-muted mb-3" style="font-size: 11px; letter-spacing: 1px;">Masukkan Kode 6 Digit</label>
                <div class="d-flex justify-content-center gap-2">
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="0" autofocus>
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="1">
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="2">
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="3">
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="4">
                    <input type="text" class="otp-input form-control {{ $isPasswordReset ?? false ? 'mode-reset' : '' }}" maxlength="1" data-index="5">
                </div>
                <input type="hidden" name="code" id="otp-code">
            </div>

            <!-- TIMER -->
            <div class="mb-4">
                <small class="text-muted" style="font-size: 13px;">
                    Kode akan kedaluwarsa dalam <span id="timer" style="color: #ff5722; font-weight: 700; font-size: 14px;">01:00</span>
                </small>
            </div>

            <!-- BUTTON -->
            <button type="submit" id="verify-btn" class="btn-verify" disabled>
                <i class="fa fa-check-circle me-1"></i>
                <span id="btn-text">{{ $isPasswordReset ?? false ? 'Verifikasi OTP' : 'Verifikasi Nomor Lama' }}</span>
            </button>
        </form>

        <!-- RESEND OTP -->
        <div class="mt-4">
            <small class="text-muted" style="font-size: 13px;">
                Belum menerima kode?
                <a href="#" id="resend-otp" class="resend-link ms-1" style="pointer-events: none; opacity: 0.5;">
                    Kirim Ulang
                </a>
            </small>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Current step tracking
let currentStep = {{ $step ?? 1 }};
const isPasswordReset = {{ $isPasswordReset ?? false ? 'true' : 'false' }};

// Update UI based on step
function updateStepUI() {
    if (isPasswordReset) {
        return; // No step indicator for password reset
    }
    
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const line1 = document.getElementById('line-1');
    const btnText = document.getElementById('btn-text');
    
    if (currentStep === 1) {
        step1.style.background = '#ff5722';
        step1.style.color = '#fff';
        step2.style.background = '#ddd';
        step2.style.color = '#999';
        line1.style.background = '#ddd';
        if (btnText) btnText.textContent = 'Verifikasi Nomor Lama';
    } else if (currentStep === 2) {
        step1.style.background = '#4caf50';
        step1.style.color = '#fff';
        step2.style.background = '#ff5722';
        step2.style.color = '#fff';
        line1.style.background = '#4caf50';
        if (btnText) btnText.textContent = 'Verifikasi Nomor Baru';
    }
}

// Initialize UI
updateStepUI();

// OTP Input Handler
const otpInputs = document.querySelectorAll('.otp-input');
const otpCodeInput = document.getElementById('otp-code');
const verifyBtn = document.getElementById('verify-btn');

otpInputs.forEach((input, index) => {
    // Auto focus next input
    input.addEventListener('input', function(e) {
        const value = this.value;
        
        // Hanya angka
        this.value = value.replace(/[^0-9]/g, '');
        
        // Update hidden input
        updateOtpCode();
        
        // Auto focus next
        if (this.value.length === 1 && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
        
        // Enable/disable button
        checkOtpComplete();
    });
    
    // Handle backspace
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
    
    // Handle paste
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
        
        for (let i = 0; i < pasteData.length && index + i < otpInputs.length; i++) {
            otpInputs[index + i].value = pasteData[i];
        }
        
        updateOtpCode();
        checkOtpComplete();
        
        // Focus last filled input
        const lastIndex = Math.min(index + pasteData.length, otpInputs.length - 1);
        otpInputs[lastIndex].focus();
    });
});

function updateOtpCode() {
    let code = '';
    otpInputs.forEach(input => {
        code += input.value;
    });
    otpCodeInput.value = code;
}

function checkOtpComplete() {
    let isComplete = true;
    otpInputs.forEach(input => {
        if (input.value === '') {
            isComplete = false;
        }
    });
    
    verifyBtn.disabled = !isComplete;
    
    if (isComplete) {
        verifyBtn.style.opacity = '1';
    } else {
        verifyBtn.style.opacity = '0.6';
    }
}

// Timer Countdown
let timeLeft = 60;
let countdown;
const timerDisplay = document.getElementById('timer');
const resendBtn = document.getElementById('resend-otp');

function startCountdown() {
    countdown = setInterval(() => {
        timeLeft--;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        if (timerDisplay) {
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            if (timerDisplay) {
                timerDisplay.textContent = '00:00';
                timerDisplay.style.color = '#dc3545';
            }
            
            // Enable resend button
            if (resendBtn) {
                resendBtn.style.pointerEvents = 'auto';
                resendBtn.style.opacity = '1';
            }
        }
    }, 1000);
}

// Start countdown on page load
startCountdown();

if (resendBtn) {
    resendBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (timeLeft > 0) return;
        
        const phone = document.getElementById('phone-input').value;
        
        Swal.fire({
            icon: 'question',
            title: 'Kirim Ulang OTP?',
            text: 'Kode OTP baru akan dikirim ke nomor ini',
            showCancelButton: true,
            confirmButtonColor: '#ff5722',
            cancelButtonColor: '#999',
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mengirim...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send resend OTP request
                fetch('/profile/resend-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Gagal mengirim OTP');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Cek apakah response sukses
                    if (data.success || (data.message && data.message.includes('berhasil'))) {
                        Swal.fire({
                            icon: 'success',
                            title: 'OTP Terkirim',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        // Reset timer
                        timeLeft = 60;
                        if (resendBtn) {
                            resendBtn.style.pointerEvents = 'none';
                            resendBtn.style.opacity = '0.5';
                        }
                        if (timerDisplay) {
                            timerDisplay.style.color = '#ff5722';
                        }
                        
                        // Restart countdown
                        clearInterval(countdown);
                        startCountdown();
                        
                        // Clear OTP inputs
                        otpInputs.forEach(input => {
                            input.value = '';
                            input.style.borderColor = '#ddd';
                        });
                        otpCodeInput.value = '';
                        verifyBtn.disabled = true;
                        verifyBtn.style.opacity = '0.6';
                        otpInputs[0].focus();
                    } else {
                        throw new Error(data.message || 'Gagal mengirim OTP');
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
}

// Handle Form Submit
document.getElementById('otp-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('verify-btn');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    const successAlert = document.getElementById('success-alert');
    const successMessage = document.getElementById('success-message');
    
    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memverifikasi...';
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
    .then(response => response.json())
    .then(data => {
        // PASSWORD RESET MODE - Redirect to new password form
        if (data.step === 'password_reset') {
            Swal.fire({
                icon: 'success',
                title: 'Verifikasi Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = data.redirect;
            });
            return;
        }
        
        // TAHAP 1 SELESAI - Lanjut ke TAHAP 2
        if (data.step === 2) {
            // Show success message
            if (successMessage) successMessage.textContent = data.message;
            if (successAlert) successAlert.style.display = 'block';
            
            // Update step
            currentStep = 2;
            const stepInput = document.getElementById('step-input');
            const phoneInput = document.getElementById('phone-input');
            const phoneDisplay = document.getElementById('phone-display');
            const stepDescription = document.getElementById('step-description');
            
            if (stepInput) stepInput.value = 2;
            if (phoneInput) phoneInput.value = data.next_phone;
            if (phoneDisplay) phoneDisplay.textContent = data.next_phone;
            
            // Update step description
            if (stepDescription) {
                stepDescription.innerHTML = 
                    '<strong>Tahap 2:</strong> Kode OTP telah dikirim ke nomor baru Anda<br>' +
                    '<strong>' + data.next_phone + '</strong>';
            }
            
            // Update UI
            updateStepUI();
            
            // Clear OTP inputs
            otpInputs.forEach(input => {
                input.value = '';
                input.style.borderColor = '#ddd';
            });
            otpCodeInput.value = '';
            btn.disabled = true;
            btn.innerHTML = '<span id="btn-text">Verifikasi Nomor Baru</span>';
            btn.style.opacity = '0.6';
            otpInputs[0].focus();
            
            // Reset timer
            timeLeft = 60;
            if (resendBtn) {
                resendBtn.style.pointerEvents = 'none';
                resendBtn.style.opacity = '0.5';
            }
            if (timerDisplay) {
                timerDisplay.style.color = '#ff5722';
            }
            clearInterval(countdown);
            startCountdown();
            
            // Auto hide success alert
            setTimeout(() => {
                if (successAlert) successAlert.style.display = 'none';
            }, 3000);
        }
        // TAHAP 2 SELESAI - Redirect ke profile
        else if (data.step === 'complete' || (data.message && data.message.includes('berhasil'))) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nomor berhasil diverifikasi dan diperbarui',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect ke halaman profile
                window.location.href = data.redirect || "{{ route('profile.index') }}";
            });
        } else {
            throw new Error(data.message || 'Verifikasi gagal');
        }
    })
    .catch(error => {
        // Show error message
        if (errorMessage) errorMessage.textContent = error.message || 'Kode OTP tidak valid';
        if (errorAlert) errorAlert.style.display = 'block';
        
        // Reset inputs
        otpInputs.forEach(input => {
            input.value = '';
            input.style.borderColor = '#dc3545';
        });
        otpInputs[0].focus();
        
        // Reset button
        btn.disabled = true;
        const buttonText = isPasswordReset ? 'Verifikasi OTP' : (currentStep === 1 ? 'Verifikasi Nomor Lama' : 'Verifikasi Nomor Baru');
        btn.innerHTML = '<span id="btn-text">' + buttonText + '</span>';
        btn.style.opacity = '0.6';
        
        // Auto hide alert after 3 seconds
        setTimeout(() => {
            if (errorAlert) errorAlert.style.display = 'none';
            otpInputs.forEach(input => {
                input.style.borderColor = '#ddd';
            });
        }, 3000);
    });
});
</script>
@endpush
@endsection