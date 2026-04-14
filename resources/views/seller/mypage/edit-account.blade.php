@extends('frontend.masterseller')

@section('content')
<style>
    .edit-account-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }
    
    /* Content Wrapper */
    .edit-account-content {
        padding: 1rem;
    }
    
    .edit-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .edit-card-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #333;
    }
    
    .avatar-upload-section {
        text-align: center;
    }
    
    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ff5722;
        margin: 0 auto 1rem;
        display: block;
    }
    
    .avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #a80b0b;
        margin-bottom: 1rem;
    }
    
    .upload-btn {
        background: #fff;
        border: 2px solid #a80b0b;
        color: #a80b0b;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-block;
        transition: all 0.3s;
    }
    
    .upload-btn:hover {
        background: #a80b0b;
        color: #fff;
    }
    
    .upload-hint {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
        font-size: 0.9rem;
    }
    
    .form-label .required {
        color: #dc3545;
    }
    
    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #a80b0b;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
    }
    
    .form-hint {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .submit-btn {
        width: 100%;
        padding: 0.875rem;
        background: #a80b0b;
        border: none;
        color: #fff;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 3rem;
    }
    
    .submit-btn:hover {
        background: #0056b3;
        transform: translateY(-1px);
    }
    
    .error-alert {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .error-alert ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    
    .error-alert li {
        margin-bottom: 0.25rem;
    }
    
    .error-alert li:last-child {
        margin-bottom: 0;
    }
</style>

<div class="edit-account-container">
    
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.mypage.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Edit Akun
        </div>
        <div class="create-header-spacer"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="edit-account-content">
        @if($errors->any())
        <div class="error-alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('seller.mypage.update-account') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Avatar Section -->
            <div class="edit-card">
                <div class="edit-card-title">Foto Profil</div>
                <div class="avatar-upload-section">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" 
                             alt="Avatar" 
                             id="avatar-preview"
                             class="avatar-preview">
                    @else
                        <div id="avatar-preview-default" class="avatar-placeholder">
                            <i class="fa fa-user" style="font-size: 50px; color: #fff;"></i>
                        </div>
                        <img src="" alt="Avatar" id="avatar-preview" class="avatar-preview" style="display: none;">
                    @endif

                    <label for="avatar" class="upload-btn">
                        <i class="fa fa-camera"></i> Ubah Foto
                    </label>
                    <input type="file" id="avatar" name="avatar" style="display: none;" accept="image/*">
                    <p class="upload-hint">Format: JPG, PNG. Max: 2MB</p>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="edit-card">
                <div class="edit-card-title">Informasi Dasar</div>

                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Lengkap <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-input" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        Nomor Telepon <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-input" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $user->phone) }}" 
                           required>
                    <small class="form-hint">Format: 08xxxxxxxxxx</small>
                </div>
            </div>

            <!-- Change Password -->
            <div class="edit-card">
                <div class="edit-card-title">Ubah Password (Opsional)</div>

                <div class="form-group">
                    <label for="current_password" class="form-label">Password Lama</label>
                    <input type="password" 
                           class="form-input" 
                           id="current_password" 
                           name="current_password">
                    <small class="form-hint">Isi jika ingin mengubah password</small>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Password Baru</label>
                    <input type="password" 
                           class="form-input" 
                           id="new_password" 
                           name="new_password">
                    <small class="form-hint">Minimal 6 karakter</small>
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" 
                           class="form-input" 
                           id="new_password_confirmation" 
                           name="new_password_confirmation">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">
                <i class="fa fa-save"></i>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const defaultPreview = document.getElementById('avatar-preview-default');
            
            preview.src = e.target.result;
            preview.style.display = 'block';
            
            if (defaultPreview) {
                defaultPreview.style.display = 'none';
            }
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection