@extends('frontend.masterseller')

@section('content')
<style>
    .edit-shop-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }
    
    /* Header dengan Back Button - sama seperti Settings */
    .edit-shop-header-bar {
        background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-height: 60px;
    }
    
    .edit-shop-header-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .edit-shop-header-back a {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    
    .edit-shop-header-back a:hover {
        opacity: 0.8;
    }
    
    .edit-shop-header-title {
        flex: 1;
        text-align: center;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        padding: 0 10px;
    }
    
    .edit-shop-header-spacer {
        width: 40px;
        flex-shrink: 0;
    }
    
    /* Content Wrapper */
    .edit-shop-content {
        padding: 1rem;
    }
    
    .shop-edit-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .shop-card-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #333;
    }
    
    .logo-upload-section {
        text-align: center;
    }
    
    .logo-preview {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        object-fit: cover;
        border: 3px solid #28a745;
        margin: 0 auto 1rem;
        display: block;
    }
    
    .logo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        background: #f8f9fa;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #28a745;
        margin-bottom: 1rem;
    }
    
    .shop-upload-btn {
        background: #fff;
        border: 2px solid #28a745;
        color: #28a745;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-block;
        transition: all 0.3s;
    }
    
    .shop-upload-btn:hover {
        background: #28a745;
        color: #fff;
    }
    
    .shop-upload-hint {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    
    .shop-form-group {
        margin-bottom: 1.25rem;
    }
    
    .shop-form-group:last-child {
        margin-bottom: 0;
    }
    
    .shop-form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
        font-size: 0.9rem;
    }
    
    .shop-form-label .required {
        color: #dc3545;
    }
    
    .shop-form-input,
    .shop-form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.3s;
        font-family: inherit;
    }
    
    .shop-form-input:focus,
    .shop-form-textarea:focus {
        outline: none;
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
    
    .shop-form-textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .shop-form-hint {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .status-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .status-info-row:last-child {
        margin-bottom: 0;
    }
    
    .status-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .status-value {
        font-weight: 500;
        color: #333;
    }
    
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .status-badge.active {
        background: #28a745;
        color: #fff;
    }
    
    .status-badge.pending {
        background: #ffc107;
        color: #333;
    }
    
    .status-slug {
        color: #007bff;
        font-size: 0.875rem;
    }
    
    .warning-alert {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 0.875rem;
        border-radius: 8px;
        font-size: 0.85rem;
        margin-top: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .warning-alert i {
        margin-top: 0.125rem;
    }
    
    .shop-submit-btn {
        width: 100%;
        padding: 0.875rem;
        background: #28a745;
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
    
    .shop-submit-btn:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    
    .shop-error-alert {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .shop-error-alert ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    
    .shop-error-alert li {
        margin-bottom: 0.25rem;
    }
    
    .shop-error-alert li:last-child {
        margin-bottom: 0;
    }

    /* CSS untuk map dan location picker */
    #address_search {
        position: relative;
    }

    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .search-result-item {
        padding: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .search-result-item:hover {
        background-color: #f5f5f5;
    }

    .search-result-item-name {
        font-weight: 500;
        color: #333;
        font-size: 0.9rem;
    }

    .search-result-item-address {
        font-size: 0.75rem;
        color: #666;
        margin-top: 2px;
    }
</style>

<div class="edit-shop-container">
    
    <!-- Header dengan Back Button -->
    <div class="edit-shop-header-bar">
        <div class="edit-shop-header-back">
            <a href="{{ route('seller.mypage.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="edit-shop-header-title">
            Edit Data Toko
        </div>
        <div class="edit-shop-header-spacer"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="edit-shop-content">
        @if($errors->any())
        <div class="shop-error-alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('seller.mypage.update-shop') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Logo Section -->
            <div class="shop-edit-card">
                <div class="shop-card-title">Logo Toko</div>
                <div class="logo-upload-section">
                    @if($shop->logo)
                        <img src="{{ asset('storage/' . $shop->logo) }}" 
                             alt="Logo" 
                             id="logo-preview"
                             class="logo-preview">
                    @else
                        <div id="logo-preview-default" class="logo-placeholder">
                            <i class="fa fa-store" style="font-size: 50px; color: #6c757d;"></i>
                        </div>
                        <img src="" alt="Logo" id="logo-preview" class="logo-preview" style="display: none;">
                    @endif

                    <label for="logo" class="shop-upload-btn">
                        <i class="fa fa-camera"></i> Ubah Logo
                    </label>
                    <input type="file" id="logo" name="logo" style="display: none;" accept="image/*">
                    <p class="shop-upload-hint">Format: JPG, PNG. Max: 2MB</p>
                </div>
            </div>

            <!-- Shop Info -->
            <div class="shop-edit-card">
                <div class="shop-card-title">Informasi Toko</div>

                <div class="shop-form-group">
                    <label for="name_store" class="shop-form-label">
                        Nama Toko <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="shop-form-input" 
                           id="name_store" 
                           name="name_store" 
                           value="{{ old('name_store', $shop->name_store) }}" 
                           required>
                    <small class="shop-form-hint">Nama toko akan otomatis menjadi URL slug</small>
                </div>

                <div class="shop-form-group">
                    <label for="description" class="shop-form-label">Deskripsi Toko</label>
                    <textarea class="shop-form-textarea" 
                              id="description" 
                              name="description" 
                              rows="4">{{ old('description', $shop->description) }}</textarea>
                    <small class="shop-form-hint">Ceritakan tentang toko Anda</small>
                </div>

                <!-- FITUR BARU: Lokasi Toko dengan Map -->
                <div class="shop-form-group">
                    <label class="shop-form-label">
                        <i class="fa fa-map-marker-alt"></i> Lokasi Toko <span class="required">*</span>
                    </label>

                    <!-- Tombol untuk "Lokasi Saat Ini" dan "Cari di Peta" -->
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <button type="button" class="shop-form-input" id="btnCurrentLocation" 
                                style="flex: 1; background: #fff; border: 2px solid #28a745; color: #28a745; font-weight: 600; cursor: pointer; padding: 0.75rem;">
                            <i class="fa fa-location-arrow"></i> Lokasi Saat Ini
                        </button>
                        <button type="button" class="shop-form-input active" id="btnSearchLocation" 
                                style="flex: 1; background: #28a745; border: 2px solid #28a745; color: #fff; font-weight: 600; cursor: pointer; padding: 0.75rem;">
                            <i class="fa fa-search"></i> Cari Lokasi
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div id="searchWrapper" style="position: relative; margin-bottom: 1rem;">
                        <input type="text" 
                               id="address_search" 
                               class="shop-form-input"
                               placeholder="Ketik nama lokasi atau alamat...">
                        <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
                    </div>

                    <!-- Peta -->
                    <div id="shopMap" style="height: 350px; border-radius: 8px; overflow: hidden; border: 2px solid #ddd; margin-bottom: 1rem;"></div>

                    <!-- Koordinat -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="shop-form-group" style="margin-bottom: 0;">
                            <label for="latitude" class="shop-form-label">Latitude <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   class="shop-form-input" 
                                   id="latitude" 
                                   name="latitude" 
                                   value="{{ old('latitude', $shop->latitude) }}"
                                   required>
                        </div>
                        <div class="shop-form-group" style="margin-bottom: 0;">
                            <label for="longitude" class="shop-form-label">Longitude <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   class="shop-form-input" 
                                   id="longitude" 
                                   name="longitude" 
                                   value="{{ old('longitude', $shop->longitude) }}"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Alamat Toko (auto-fill dari map) -->
                <div class="shop-form-group">
                    <label for="address_store" class="shop-form-label">
                        Alamat Lengkap <span class="required">*</span>
                    </label>
                    <textarea class="shop-form-textarea" 
                              id="address_store" 
                              name="address_store" 
                              rows="3" 
                              required>{{ old('address_store', $shop->address_store) }}</textarea>
                    <small class="shop-form-hint">Alamat akan diperbarui otomatis dari peta</small>
                </div>
            </div>

            <!-- Shop Status -->
            <div class="shop-edit-card">
                <div class="shop-card-title">Status Toko</div>
                
                <div class="status-info-row">
                    <span class="status-label">Status Aktif</span>
                    @if($shop->is_active)
                        <span class="status-badge active">
                            <i class="fa fa-check-circle"></i> Toko Aktif
                        </span>
                    @else
                        <span class="status-badge pending">
                            <i class="fa fa-clock"></i> Menunggu Verifikasi
                        </span>
                    @endif
                </div>

                <div class="status-info-row">
                    <span class="status-label">URL Toko</span>
                    <span class="status-slug">{{ $shop->slug }}</span>
                </div>

                @if(!$shop->is_active)
                <div class="warning-alert">
                    <i class="fa fa-info-circle"></i>
                    <small>Toko Anda sedang dalam proses verifikasi oleh admin. Setelah disetujui, toko akan aktif secara otomatis.</small>
                </div>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="shop-submit-btn">
                <i class="fa fa-save"></i>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            const defaultPreview = document.getElementById('logo-preview-default');
            
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

<!-- Leaflet CSS dan JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="{{ asset('js/seller-location-picker.js') }}"></script>
@endsection