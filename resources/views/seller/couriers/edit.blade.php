@extends('frontend.masterseller')

@section('content')
<style>
    .form-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding-bottom: 80px;
    }
    
    .form-header-bar {
        background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-header-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-header-back a {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
    }
    
    .form-header-title {
        flex: 1;
        text-align: center;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
    }
    
    .form-header-spacer {
        width: 40px;
    }
    
    .form-card {
        background: #fff;
        margin: 1rem;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .form-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .form-label .required {
        color: #dc3545;
        margin-left: 0.25rem;
    }
    
    .form-control {
        width: 100%;
        height: 48px;
        padding: 0 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
        background: #fff;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #ff5722;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
    
    .form-hint {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #6c757d;
    }
    
    .input-group {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.125rem;
        pointer-events: none;
    }
    
    .form-control.has-icon {
        padding-left: 3rem;
    }
    
    .info-card {
        background: #e3f2fd;
        border-left: 4px solid #1976d2;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .info-card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1976d2;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card-text {
        font-size: 0.875rem;
        color: #0d47a1;
        line-height: 1.5;
        margin: 0;
    }
    
    .warning-card {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .warning-card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #856404;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .warning-card-text {
        font-size: 0.875rem;
        color: #856404;
        line-height: 1.5;
        margin: 0;
    }
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2rem;
    }
    
    .btn {
        flex: 1;
        height: 50px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-cancel {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #dee2e6;
    }
    
    .btn-cancel:hover {
        background: #e9ecef;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    .btn-submit:hover {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
        box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4);
        transform: translateY(-2px);
    }
    
    .courier-status-card {
        background: #fff;
        margin: 1rem;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .status-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .status-value {
        font-size: 0.875rem;
        color: #333;
        font-weight: 600;
    }
    
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .reset-password-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px dashed #e0e0e0;
    }
</style>

<div class="form-container">
    <!-- Header -->
    <div class="form-header-bar">
        <div class="form-header-back">
            <a href="{{ route('seller.couriers.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="form-header-title">
            Edit Data Kurir
        </div>
        <div class="form-header-spacer"></div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
        <i class="fa fa-exclamation-circle"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Status Card -->
    <div class="courier-status-card">
        <div class="status-item">
            <span class="status-label">
                <i class="fa fa-store"></i> Toko
            </span>
            <span class="status-value">{{ $shop->name_store }}</span>
        </div>
        <div class="status-item">
            <span class="status-label">
                <i class="fa fa-calendar-plus"></i> Dibuat
            </span>
            <span class="status-value">{{ $courier->created_at->format('d M Y, H:i') }}</span>
        </div>
        <div class="status-item">
            <span class="status-label">
                <i class="fa fa-user-tie"></i> Dibuat oleh
            </span>
            <span class="status-value">{{ $courier->creator->name }}</span>
        </div>
        <div class="status-item">
            <span class="status-label">
                <i class="fa fa-toggle-on"></i> Status
            </span>
            <span class="status-badge {{ $courier->status }}">
                {{ ucfirst($courier->status) }}
            </span>
        </div>
    </div>

    <!-- Info Card -->
    <div style="padding: 0 1rem; margin-top: 1rem;">
        <div class="info-card">
            <div class="info-card-title">
                <i class="fa fa-info-circle"></i>
                Informasi
            </div>
            <p class="info-card-text">
                Anda dapat mengubah nama dan nomor HP kurir. Untuk mengubah password, gunakan tombol "Reset Password" di bawah.
            </p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('seller.couriers.update', $courier->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-card">
            <div class="form-section-title">
                <i class="fa fa-user-circle"></i>
                Data Kurir
            </div>

            <!-- Nama -->
            <div class="form-group">
                <label class="form-label">
                    Nama Lengkap
                    <span class="required">*</span>
                </label>
                <div class="input-group">
                    <i class="fa fa-user input-icon"></i>
                    <input type="text" 
                           name="name" 
                           class="form-control has-icon @error('name') is-invalid @enderror" 
                           placeholder="Masukkan nama lengkap kurir"
                           value="{{ old('name', $courier->user->name) }}"
                           required>
                </div>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nomor HP -->
            <div class="form-group">
                <label class="form-label">
                    Nomor HP
                    <span class="required">*</span>
                </label>
                <div class="input-group">
                    <i class="fa fa-phone input-icon"></i>
                    <input type="tel" 
                           name="phone" 
                           class="form-control has-icon @error('phone') is-invalid @enderror" 
                           placeholder="Contoh: 081234567890"
                           value="{{ old('phone', $courier->user->phone) }}"
                           pattern="[0-9]{10,15}"
                           required>
                </div>
                <span class="form-hint">
                    <i class="fa fa-lightbulb"></i>
                    Nomor HP digunakan untuk login. Minimal 10 digit, maksimal 15 digit.
                </span>
                @error('phone')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <!-- Reset Password Section -->
            <div class="reset-password-section">
                <div class="warning-card">
                    <div class="warning-card-title">
                        <i class="fa fa-lock"></i>
                        Reset Password
                    </div>
                    <p class="warning-card-text">
                        Jika kurir lupa password, Anda bisa mereset password dengan menekan tombol di bawah. 
                        Password baru akan di-generate secara otomatis dan <strong>dikirim langsung ke WhatsApp kurir</strong>.
                    </p>
                </div>
                
                <form action="{{ route('seller.couriers.reset-password', $courier->id) }}" 
                      method="POST" 
                      style="margin: 0;"
                      onsubmit="return confirm('Yakin ingin mereset password kurir ini? Password baru akan di-generate otomatis.')">
                    @csrf
                    <button type="submit" class="btn btn-warning" style="width: 100%;">
                        <i class="fa fa-key"></i>
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions -->
        <div style="padding: 0 1rem;">
            <div class="form-actions">
                <a href="{{ route('seller.couriers.index') }}" class="btn btn-cancel">
                    <i class="fa fa-times"></i>
                    Batal
                </a>
                <button type="submit" class="btn btn-submit">
                    <i class="fa fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Validasi nomor HP (hanya angka)
document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endsection