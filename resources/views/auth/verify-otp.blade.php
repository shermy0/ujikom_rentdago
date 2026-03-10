@extends('frontend.master')

@section('navbar')
    <div class="mobile-top-header">
        <a href="{{ route('auth.register') }}" style="color: #fff; font-size: 20px; margin-right: 15px;">
            <i class="fa fa-arrow-left"></i>
        </a>
        <span style="color: #fff; font-size: 16px; font-weight: 500;">Verifikasi OTP</span>
    </div>
@endsection

@section('content')
<div class="container py-4" style="background: #fff; min-height: calc(100vh - 115px);">

    <!-- ICON -->
    <div class="text-center mb-4">
        <div style="width: 80px; height: 80px; background: #fff3f0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <i class="fa fa-envelope" style="font-size: 35px; color: #ff5722;"></i>
        </div>
        <h4 class="font-weight-bold" style="color: #333;">Verifikasi Nomor</h4>
        <p class="text-muted small">
            Masukkan kode OTP yang telah dikirim ke<br>
            <strong id="phone-display">{{ request('phone') }}</strong>
        </p>
    </div>

    <!-- ALERT ERROR -->
    <div id="error-alert" class="alert alert-danger" style="display: none; border-radius: 10px;">
        <small id="error-message"></small>
    </div>

    <!-- OTP FORM -->
    <form id="otp-form" action="{{ route('auth.verify.otp.post') }}" method="POST">
        @csrf
        <input type="hidden" name="phone" id="phone-input" value="{{ request('phone') }}">

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
                Kode akan kedaluwarsa dalam <span id="timer" style="color: #ff5722; font-weight: bold;">01:00</span>
            </small>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                id="verify-btn"
                class="btn btn-block text-white rounded-pill mt-4"
                style="background:#ff5722; height: 45px; font-weight: 500;"
                disabled>
            Verifikasi
        </button>
    </form>

    <!-- RESEND OTP -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Tidak menerima kode?
            <a href="#" 
               id="resend-otp"
               class="font-weight-bold"
               style="color:#ff5722; pointer-events: none; opacity: 0.5;">
                Kirim Ulang
            </a>
        </small>
    </div>
</div>

@push('scripts')
<script>
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
        
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerDisplay.textContent = '00:00';
            timerDisplay.style.color = '#dc3545';
            
            // Enable resend button
            resendBtn.style.pointerEvents = 'auto';
            resendBtn.style.opacity = '1';
        }
    }, 1000);
}

// Start countdown on page load
startCountdown();

resendBtn.addEventListener('click', function(e) {
    e.preventDefault();
    
    if (timeLeft > 0) return;
    
    const phone = document.getElementById('phone-input').value;
    
    Swal.fire({
        icon: 'question',
        title: 'Kirim Ulang OTP?',
        text: 'Kode OTP baru akan dikirim ke nomor Anda',
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
            fetch('/auth/resend-otp', {
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
                    resendBtn.style.pointerEvents = 'none';
                    resendBtn.style.opacity = '0.5';
                    timerDisplay.style.color = '#ff5722';
                    
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

// Handle Form Submit
document.getElementById('otp-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('verify-btn');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    
    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memverifikasi...';
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
    .then(response => response.json())
    .then(data => {
        if (data.message && data.message.includes('berhasil')) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nomor berhasil diverifikasi',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Redirect ke halaman home
                window.location.href = data.redirect || "{{ route('home') }}";
            });
        } else {
            throw new Error(data.message || 'Verifikasi gagal');
        }
    })
    .catch(error => {
        // Show error message
        errorMessage.textContent = error.message || 'Kode OTP tidak valid';
        errorAlert.style.display = 'block';
        
        // Reset inputs
        otpInputs.forEach(input => {
            input.value = '';
            input.style.borderColor = '#dc3545';
        });
        otpInputs[0].focus();
        
        // Reset button
        btn.disabled = true;
        btn.innerHTML = 'Verifikasi';
        btn.style.opacity = '0.6';
        
        // Auto hide alert after 3 seconds
        setTimeout(() => {
            errorAlert.style.display = 'none';
            otpInputs.forEach(input => {
                input.style.borderColor = '#ddd';
            });
        }, 3000);
    });
});
</script>
@endpush
@endsection