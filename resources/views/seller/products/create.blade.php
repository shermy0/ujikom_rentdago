@extends('frontend.masterseller')

@section('content')
<style>
    .create-product-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }

    .form-section {
        padding: 1rem;
    }
    
    .form-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .form-card-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #333;
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
    
    .required {
        color: #dc3545;
    }
    
    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
        font-family: inherit;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #ff5722;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-hint {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .invalid-feedback {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.8rem;
        color: #dc3545;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .checkbox-wrapper label {
        margin: 0;
        cursor: pointer;
        font-weight: 400;
    }
    
    .image-upload-section {
        text-align: center;
        padding: 1.5rem;
        border: 2px dashed #ddd;
        border-radius: 8px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .image-upload-section:hover {
        border-color: #ff5722;
        background: #fff;
    }
    
    .image-upload-section i {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .image-upload-section p {
        margin: 0.5rem 0 0 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .image-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .image-preview-item {
        position: relative;
        padding-top: 100%;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
    }
    
    .image-preview-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .submit-btn {
        width: 100%;
        padding: 0.875rem;
        background: #A20B0B;
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
        background: #770C0C;
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
</style>

<div class="create-product-container">
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.products.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Tambah Produk
        </div>
        <div class="create-header-spacer"></div>
    </div>


    <div class="form-section">
        @if($errors->any())
        <div class="error-alert">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Basic Info -->
            <div class="form-card">
                <div class="form-card-title">Informasi Dasar</div>

                <div class="form-group">
                    <label for="category_id" class="form-label">
                        Kategori <span class="required">*</span>
                    </label>
                    <select name="category_id" 
                            id="category_id" 
                            class="form-select @error('category_id') is-invalid @enderror" 
                            required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Barang <span class="required">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-input @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}"
                           placeholder="Contoh: Canon EOS R5"
                           required>
                    @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="condition" class="form-label">Kondisi</label>
                    <input type="text" 
                           name="condition" 
                           id="condition" 
                           class="form-input @error('condition') is-invalid @enderror" 
                           value="{{ old('condition') }}"
                           placeholder="Contoh: Baru / Bekas Baik">
                    @error('condition')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="form-card">
                <div class="form-card-title">Deskripsi</div>

                <div class="form-group">
                    <label for="description" class="form-label">Deskripsi Produk</label>
                    <textarea name="description" 
                              id="description" 
                              class="form-textarea @error('description') is-invalid @enderror"
                              placeholder="Jelaskan detail barang, spesifikasi, kelengkapan, dll">{{ old('description') }}</textarea>
                    @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Images -->
            <div class="form-card">
                <div class="form-card-title">Foto Barang</div>

                <div class="form-group">
                    <input type="file" 
                           name="images[]" 
                           id="images" 
                           multiple 
                           accept="image/jpeg,image/png,image/jpg" 
                           style="display: none;"
                           class="@error('images.*') is-invalid @enderror">

                    <label for="images" class="image-upload-section">
                        <i class="fa fa-camera"></i>
                        <p><strong>Klik untuk tambah foto</strong></p>
                        <p>Format: JPG, PNG &bull; Max: 2MB per foto<br>Bisa pilih banyak foto sekaligus</p>
                    </label>

                    @error('images.*')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror

                    <div class="image-preview-container" id="image-preview"></div>
                </div>
            </div>

            <!-- Status -->
            <div class="form-card">
                <div class="form-card-title">Status</div>

                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" 
                               name="is_maintenance" 
                               id="is_maintenance" 
                               value="1"
                               {{ old('is_maintenance') ? 'checked' : '' }}>
                        <label for="is_maintenance" class="form-label">
                            Barang sedang maintenance
                        </label>
                    </div>
                    <small class="form-hint">Centang jika barang sedang dalam perbaikan/maintenance</small>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="submit-btn">
                <i class="fa fa-save"></i>
                <span>Simpan Produk</span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection