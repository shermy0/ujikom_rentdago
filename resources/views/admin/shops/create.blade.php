@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-plus-circle me-2"></i>Tambah Toko Baru
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data" id="shopForm">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Pemilik Toko -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pemilik Toko <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pemilik --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">User yang belum memiliki toko</div>
                    </div>

                    <!-- Nama Toko -->
                    <div class="mb-3">
                        <label for="name_store" class="form-label">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" name="name_store" id="name_store" 
                               class="form-control @error('name_store') is-invalid @enderror" 
                               value="{{ old('name_store') }}" required>
                        @error('name_store')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label for="address_store" class="form-label">Alamat Toko <span class="text-danger">*</span></label>
                        <textarea name="address_store" id="address_store" rows="3" 
                                  class="form-control @error('address_store') is-invalid @enderror" required>{{ old('address_store') }}</textarea>
                        @error('address_store')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Logo -->
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo Toko <span class="text-danger">*</span></label>
                        <input type="file" name="logo" id="logo" 
                               class="form-control @error('logo') is-invalid @enderror"
                               accept="image/*" onchange="previewLogo(this)" required>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: JPEG, PNG, JPG, GIF. Max: 2MB</div>
                        
                        <div class="mt-3">
                            <div id="logo-preview" class="logo-preview-box">
                                <i class="bi bi-image"></i>
                                <span>Preview Logo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                   id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Toko Aktif</strong>
                            </label>
                        </div>
                        <div class="form-text">Aktifkan agar toko dapat dilihat publik</div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Simpan Toko
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    .card-header {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-body {
        padding: 25px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #d94429, #e55a2b);
    }
    .logo-preview-box {
        width: 100%;
        height: 200px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #999;
        overflow: hidden;
    }
    .logo-preview-box i {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .logo-preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewLogo(input) {
        const preview = document.getElementById('logo-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
