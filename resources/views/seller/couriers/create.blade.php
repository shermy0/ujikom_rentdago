@extends('frontend.masterseller')

@section('content')
<style>
    .form-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding-bottom: 80px;
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
    
    .success-card {
        background: #d4edda;
        border-left: 4px solid #28a745;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .success-card-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #155724;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .success-card-text {
        font-size: 0.875rem;
        color: #155724;
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
        background: linear-gradient(135deg, #a80b0b 0%, #760404 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .btn-submit:hover {
        background: linear-gradient(135deg, #760404 0%, #a80b0b 100%);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        transform: translateY(-2px);
    }

    .phone-input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    transition: 0.2s;
}

.phone-input-wrapper:focus-within {
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
}

.phone-prefix {
    padding: 10px 12px;
    background: #f5f5f5;
    border-right: 1px solid #ddd;
    font-weight: 500;
    color: #555;
}

.phone-input {
    border: none;
    outline: none;
    padding: 10px 12px;
    width: 100%;
    font-size: 14px;
}

.phone-input::placeholder {
    color: #aaa;
}
</style>

<div class="form-container">
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.couriers.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Tambah Kurir
        </div>
        <div class="create-header-spacer"></div>
    </div>
    <!-- Alert Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
        <i class="fa fa-exclamation-circle"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
        <i class="fa fa-exclamation-circle"></i>
        <strong>Terjadi kesalahan:</strong>
        <ul style="margin: 0.5rem 0 0 1.2rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

    <!-- Info Cards -->
    <div style="padding: 0 1rem; margin-top: 1rem;">
        <div class="info-card">
            <div class="info-card-title">
                <i class="fa fa-info-circle"></i>
                Informasi Penting
            </div>
            <p class="info-card-text">
                Kurir yang Anda tambahkan akan terikat dengan toko <strong>{{ $shop->name_store }}</strong>. 
                Password akan di-generate otomatis dan ditampilkan setelah kurir berhasil ditambahkan.
            </p>
        </div>
        
<div class="success-card">
    <div class="success-card-title">
        <i class="fa fa-whatsapp"></i>
        Password Otomatis via WhatsApp
    </div>

    <ul class="success-card-text" style="padding-left: 1.2rem; margin: 0;">
        <li>
            Sistem akan membuat password acak <strong>(8 karakter)</strong> secara otomatis.
        </li>
        <li>
            <strong>Password akan dikirim langsung ke nomor WhatsApp kurir.</strong>
        </li>
        <li>
            Jika pengiriman gagal, password default <strong>123456</strong> akan digunakan.
        </li>
        <li>
            <strong>Kurir disarankan mengganti password setelah login pertama.</strong>
        </li>
    </ul>
</div>
    </div>

    <!-- Form -->
    <form action="{{ route('seller.couriers.store') }}" method="POST">
        @csrf
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="id" value="{{ request('id') }}">

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
                           value="{{ old('name') }}"
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

    <div class="phone-input-wrapper">
        <span class="phone-prefix">+62</span>
        
        <input type="tel" 
               name="phone" 
               class="phone-input @error('phone') is-invalid @enderror" 
               placeholder="81234567890"
               value="{{ old('phone') }}"
               required>
    </div>

    <span class="form-hint">
        <i class="fa fa-lightbulb"></i>
        Masukkan nomor tanpa 0 di depan. Contoh: 81234567890
    </span>

    @error('phone')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>

        <!-- Actions -->
        <div style="padding: 0 1rem;">
            <div class="form-actions">
                <a href="{{ route('seller.couriers.index') }}" class="btn btn-cancel">
                    <i class="fa fa-times"></i>
                    Batal
                </a>
                <button type="submit" class="btn btn-submit">
                    <i class="fa fa-check"></i>
                    Tambah Kurir
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

document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    let val = this.value.replace(/[^0-9]/g, '');

    // kalau dia ngetik 08 → ubah jadi 8
    if (val.startsWith('0')) {
        val = val.substring(1);
    }

    this.value = val;
});

document.querySelector('input[name="phone"]').addEventListener('input', function() {
    let val = this.value.replace(/[^0-9]/g, '');

    if (val.startsWith('0')) {
        val = val.substring(1);
    }

    this.value = val;
});

const phoneInput = document.querySelector('input[name="phone"]');

phoneInput.addEventListener('input', function () {
    let val = this.value.replace(/[^0-9]/g, '');

    // auto hapus 0 depan
    if (val.startsWith('0')) {
        val = val.substring(1);
    }

    this.value = val;
});

</script>


@endsection