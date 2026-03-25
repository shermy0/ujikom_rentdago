@extends('frontend.master')
@section('navbar')
    @include('frontend.navbar')
@endsection
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
    .avatar-wrapper {
        text-align: center;
        margin-top: -50px;
        margin-bottom: 25px;
    }
    .avatar-preview-container {
        display: inline-block;
        position: relative;
    }
    .avatar-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .avatar-placeholder i {
        font-size: 36px;
        color: #fff;
    }
    .camera-btn {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 32px;
        height: 32px;
        background: #ff6b35;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: 2px solid #fff;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(255,107,53,0.3);
        transition: transform 0.2s;
        z-index: 10;
        margin: 0;
    }
    .camera-btn i {
        font-size: 14px;
    }
    .camera-btn:hover {
        transform: scale(1.1);
    }
    .avatar-hint {
        display: block;
        margin-top: 10px;
        font-size: 12px;
        color: #a0aec0;
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
    .security-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        margin: 0 15px 20px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .security-info h6 {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 16px;
    }
    .security-info p {
        color: #718096;
        font-size: 13px;
        margin: 0;
    }
    .btn-reset-pw {
        background: #fff5f5;
        color: #e53e3e;
        border: 1px solid #feb2b2;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-reset-pw:hover {
        background: #e53e3e;
        color: #fff;
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
</style>

<div class="edit-profile-container">

    <!-- Alert Messages -->
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 shadow-sm" role="alert" style="border-radius: 12px; border: none;">
        <i class="fa fa-check-circle me-2"></i>{{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3 shadow-sm" role="alert" style="border-radius: 12px; border: none;">
        <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="edit-header">
        <div class="edit-header-top">
            <a href="{{ route('profile.index') }}" class="header-back-btn">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div class="header-title-text">Edit Profil</div>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-card">
            <div class="avatar-wrapper">
                <div class="avatar-preview-container">
                    <div id="avatar-container">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="Avatar" 
                                 id="avatar-preview"
                                 class="avatar-image">
                        @else
                            <div id="avatar-preview" class="avatar-placeholder">
                                <i class="fa fa-user"></i>
                            </div>
                        @endif
                    </div>
                    
                    <label for="avatar-input" class="camera-btn">
                        <i class="fa fa-camera"></i>
                    </label>
                    <input type="file" 
                           id="avatar-input" 
                           name="avatar" 
                           accept="image/*" 
                           class="d-none">
                </div>
                <span class="avatar-hint">Format: JPG, PNG, max 2MB</span>
            </div>

            <div class="mb-4">
                <label for="name" class="form-label-custom">Nama Lengkap</label>
                <input type="text" 
                       class="form-control-custom @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}"
                       placeholder="Masukkan nama lengkap"
                       required>
                @error('name')
                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-2">
                <label for="phone" class="form-label-custom">No. Telepon / WhatsApp</label>
                <input type="text" 
                       class="form-control-custom @error('phone') is-invalid @enderror" 
                       id="phone" 
                       name="phone" 
                       value="{{ old('phone', $user->phone) }}"
                       placeholder="Contoh: 08123456789"
                       required>
                @error('phone')
                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                @enderror
                <small class="text-muted mt-2 d-block" style="font-size: 12px;"><i class="fa fa-info-circle me-1"></i> Pastikan nomor aktif untuk verifikasi OTP</small>
            </div>
        </div>

        <!-- Security Section -->
        <div class="security-card">
            <div class="security-info">
                <h6>Keamanan Akun</h6>
                <p>Kelola password akun Anda</p>
            </div>
            <a href="{{ route('profile.reset.password') }}" class="btn-reset-pw">
                <i class="fa fa-lock me-1"></i> Reset
            </a>
        </div>

        <!-- Submit Button -->
        <div class="save-wrapper">
            <button type="submit" class="btn-save">
                <i class="fa fa-check-circle"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            this.value = '';
            return;
        }

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPEG, JPG, atau PNG');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            const container = document.getElementById('avatar-container');
            container.innerHTML = `<img src="${event.target.result}" 
                                       id="avatar-preview"
                                       class="avatar-image">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection