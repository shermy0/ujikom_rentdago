@extends('frontend.master')
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('navbar')
    <div class="mobile-top-header">
        <a href="{{ $isPasswordReset ?? false ? route('profile.reset.password') : route('profile.edit') }}" style="color: #fff; font-size: 20px; margin-right: 15px;">
            <i class="fa fa-arrow-left"></i>
        </a>
        <span style="color: #fff; font-size: 16px; font-weight: 500;">
            {{ $isPasswordReset ?? false ? 'Verifikasi Reset Password' : 'Verifikasi Perubahan Nomor' }}
        </span>
    </div>
@endsection

@section('content')
<div class="container py-4" style="background: #fff; min-height: calc(100vh - 115px);">

    <!-- ICON -->
    <div class="text-center mb-4">
        <div style="width: 80px; height: 80px; background: {{ $isPasswordReset ?? false ? '#fff3cd' : '#fff3f0' }}; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <i class="fa {{ $isPasswordReset ?? false ? 'fa-lock' : 'fa-shield-alt' }}" style="font-size: 35px; color: {{ $isPasswordReset ?? false ? '#ffc107' : '#ff5722' }};"></i>
        </div>
        <h4 class="font-weight-bold" style="color: #333;">
            {{ $isPasswordReset ?? false ? 'Verifikasi untuk Reset Password' : 'Verifikasi Perubahan Nomor' }}
        </h4>
        
        @if($isPasswordReset ?? false)
            <!-- PASSWORD RESET MODE -->
            <p class="text-muted small">
                Kode OTP telah dikirim ke nomor WhatsApp Anda<br>
                <strong id="phone-display">{{ $phone }}</strong>
            </p>
        @else
            <!-- STEP INDICATOR -->
            <div class="d-flex justify-content-center align-items-center mt-3 mb-3">
                <div class="step-indicator" id="step-1" style="width: 40px; height: 40px; border-radius: 50%; background: #ff5722; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    1
                </div>
                <div style="width: 50px; height: 2px; background: #ddd;" id="line-1"></div>
                <div class="step-indicator" id="step-2" style="width: 40px; height: 40px; border-radius: 50%; background: #ddd; color: #999; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    2
                </div>
            </div>

            <p class="text-muted small" id="step-description">
                @if(isset($step) && $step == 2)
                    <strong>Tahap 2:</strong> Kode OTP telah dikirim ke nomor baru Anda<br>
                    <strong id="phone-display">{{ $phone }}</strong>
                @else
                    <strong>Tahap 1:</strong> Kode OTP telah dikirim ke nomor lama Anda<br>
                    <strong id="phone-display">{{ $phone }}</strong>
                @endif
            </p>
            
            @if(isset($newPhone))
            <div class="alert alert-info mt-3" style="border-radius: 10px; font-size: 13px;">
                <i class="fa fa-info-circle"></i> Nomor akan diubah menjadi: <strong>{{ $newPhone }}</strong>
            </div>
            @endif
        @endif
    </div>

    <!-- ALERT ERROR -->
    <div id="error-alert" class="alert alert-danger" style="display: none; border-radius: 10px;">
        <small id="error-message"></small>
    </div>

    <!-- ALERT SUCCESS -->
    <div id="success-alert" class="alert alert-success" style="display: none; border-radius: 10px;">
        <small id="success-message"></small>
    </div>

    <!-- OTP FORM -->
    <form id="otp-form" action="{{ route('profile.verify.otp.post') }}" method="POST">
        @csrf
        <input type="hidden" name="phone" id="phone-input" value="{{ $phone }}">
        <input type="hidden" name="step" id="step-input" value="{{ $step ?? 1 }}">

        <!-- OTP INPUT -->
        <div class="form-group">
            <label class="small font-weight-bold text-center d-block" style="color: #333;">Kode OTP</label>
            <div class="d-flex justify-content-center gap-2" style="gap: 10px;">
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="0"
                       autofocus>
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="1">
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="2">
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="3">
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="4">
                <input type="text" 
                       class="otp-input form-control text-center" 
                       maxlength="1" 
                       style="width: 50px; height: 50px; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 10px;"
                       data-index="5">
            </div>
            <input type="hidden" name="code" id="otp-code">
        </div>

        <!-- TIMER -->
        <div class="text-center mt-3 mb-3">
            <small class="text-muted">
                Kode akan kedaluwarsa dalam <span id="timer" style="color: {{ $isPasswordReset ?? false ? '#ffc107' : '#ff5722' }}; font-weight: bold;">01:00</span>
            </small>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                id="verify-btn"
                class="btn btn-block text-white rounded-pill mt-4"
                style="background:{{ $isPasswordReset ?? false ? '#ffc107' : '#ff5722' }}; height: 45px; font-weight: 500;"
                disabled>
            <span id="btn-text">{{ $isPasswordReset ?? false ? 'Verifikasi OTP' : 'Verifikasi Nomor Lama' }}</span>
        </button>
    </form>

    <!-- RESEND OTP -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Tidak menerima kode?
            <a href="#" 
               id="resend-otp"
               class="font-weight-bold"
               style="color:{{ $isPasswordReset ?? false ? '#ffc107' : '#ff5722' }}; pointer-events: none; opacity: 0.5;">
                Kirim Ulang
            </a>
        </small>
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
            confirmButtonColor: isPasswordReset ? '#ffc107' : '#ff5722',
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
                            timerDisplay.style.color = isPasswordReset ? '#ffc107' : '#ff5722';
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