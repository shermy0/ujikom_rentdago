@extends('frontend.master')

@section('navbar')
    <div class="mobile-top-header">
        <a href="{{ route('login') }}" style="color: #fff; font-size: 20px; margin-right: 15px;">
            <i class="fa fa-arrow-left"></i>
        </a>
        <span style="color: #fff; font-size: 16px; font-weight: 500;">Lupa Password</span>
    </div>
@endsection

@section('content')
<div class="container py-4" style="background: #fff; min-height: calc(100vh - 115px);">

    <!-- TITLE -->
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fa fa-lock" style="font-size: 60px; color: #ff5722;"></i>
        </div>
        <h4 class="font-weight-bold" style="color: #333;">Lupa Password?</h4>
        <p class="text-muted small px-3">
            Masukkan nomor WhatsApp yang terdaftar, kami akan mengirimkan link reset password ke nomor Anda
        </p>
    </div>

    <!-- ALERT SUCCESS -->
    <div id="success-alert" class="alert alert-success" style="display: none; border-radius: 10px;">
        <i class="fa fa-check-circle me-2"></i>
        <small id="success-message"></small>
    </div>

    <!-- ALERT ERROR -->
    <div id="error-alert" class="alert alert-danger" style="display: none; border-radius: 10px;">
        <i class="fa fa-exclamation-circle me-2"></i>
        <small id="error-message"></small>
    </div>

    <!-- FORGOT PASSWORD FORM -->
    <form id="forgot-password-form" action="{{ route('auth.forgot.password.post') }}" method="POST">
        @csrf

        <!-- PHONE -->
        <div class="form-group">
            <label class="small font-weight-bold" style="color: #333;">
                Nomor WhatsApp <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="border-radius: 25px 0 0 25px; background: #f8f9fa; border-right: none;">
                        <i class="fa fa-phone" style="color: #ff5722;"></i>
                    </span>
                </div>
                <input type="text"
                       name="phone"
                       id="phone"
                       class="form-control"
                       style="border-left: none; border-radius: 0 25px 25px 0; height: 45px; padding-left: 0;"
                       placeholder="Contoh: 08123456789"
                       required
                       autofocus>
            </div>
            <small class="text-muted">
                <i class="fa fa-info-circle"></i> Pastikan nomor yang Anda masukkan terdaftar
            </small>
        </div>

        <!-- INFO BOX -->
        <div class="bg-light p-3 rounded mb-3" style="border-left: 3px solid #ff5722;">
            <small class="text-muted">
                <i class="fa fa-lightbulb text-warning"></i> 
                <strong>Catatan:</strong> Link reset password akan dikirim melalui WhatsApp dan berlaku selama 15 menit
            </small>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                id="submit-btn"
                class="btn btn-block text-white rounded-pill mt-4"
                style="background:#ff5722; height: 45px; font-weight: 500;">
            <i class="fa fa-paper-plane me-2"></i>Kirim Link Reset
        </button>
    </form>

    <!-- BACK TO LOGIN -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Sudah ingat password?
            <a href="{{ route('login') }}"
               class="font-weight-bold"
               style="color:#ff5722">
                Kembali ke Login
            </a>
        </small>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Handle Form Submit dengan AJAX
document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submit-btn');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    const successAlert = document.getElementById('success-alert');
    const successMessage = document.getElementById('success-message');
    
    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Mengirim...';
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
        if (data.success) {
            // Show success message
            successMessage.textContent = data.message;
            successAlert.style.display = 'block';
            
            // Show SweetAlert
            Swal.fire({
                icon: 'success',
                title: 'Link Terkirim!',
                html: data.message + '<br><small class="text-muted">Silakan cek WhatsApp Anda</small>',
                confirmButtonColor: '#ff5722',
                confirmButtonText: 'OK'
            }).then(() => {
                // Redirect ke login setelah 3 detik
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
            });
            
            // Reset form
            this.reset();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        // Show error message
        errorMessage.textContent = error.message || 'Terjadi kesalahan, silakan coba lagi';
        errorAlert.style.display = 'block';
        
        // Scroll to top to show error
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .finally(() => {
        // Reset button
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Kirim Link Reset';
    });
});

// Auto hide alerts after 5 seconds
setTimeout(() => {
    const errorAlert = document.getElementById('error-alert');
    const successAlert = document.getElementById('success-alert');
    if (errorAlert.style.display === 'block') {
        errorAlert.style.display = 'none';
    }
    if (successAlert.style.display === 'block') {
        successAlert.style.display = 'none';
    }
}, 5000);
</script>
@endpush
@endsection