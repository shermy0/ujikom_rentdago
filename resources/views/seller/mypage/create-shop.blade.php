@extends('frontend.masterseller')

@section('content')
<style>
    .create-shop-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }
    
    /* Content Wrapper */
    .create-shop-form {
        max-width: 600px;
        margin: 0 auto;
        padding: 1rem;
    }
    
    .form-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
        font-size: 0.9rem;
    }
    
    .form-label .required {
        color: #dc3545;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #A20B0B;
        box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
    }
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-hint {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .preview-image {
        margin-top: 0.5rem;
        max-width: 200px;
        border-radius: 8px;
        border: 2px solid #ddd;
    }
    
    .btn-submit {
        width: 100%;
        background: linear-gradient(135deg, #770C0C 0%, #A20B0B 100%);
        color: #fff;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        margin-bottom: 2rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 87, 34, 0.4);
    }
    
    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .info-box i {
        color: #2196f3;
        margin-right: 0.5rem;
    }

    /* Styling untuk map dan location picker */
    .location-section {
        margin-top: 2rem;
    }

    .location-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .btn-location {
        flex: 1;
        padding: 0.75rem;
        border: 2px solid #A20B0B;
        background: #fff;
        color: #A20B0B;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
    }

    .btn-location:hover {
        background: #A20B0B;
        color: #fff;
    }

    .btn-location.active {
        background: #A20B0B;
        color: #fff;
    }

    #shopMap {
        height: 350px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #ddd;
        margin-bottom: 1rem;
    }

    .map-info {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .coordinates-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    /* Search results dropdown */
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

    .search-input-wrapper {
        position: relative;
    }

    .loading-spinner {
        display: inline-block;
        margin-left: 0.5rem;
        color: #A20B0B;
    }
</style>

<div class="create-shop-container">

        <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.mypage.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Buka Toko
        </div>
        <div class="create-header-spacer"></div>
    </div>
    

    <!-- Content Wrapper -->
    <div class="create-shop-form">
        <div class="info-box">
            <i class="fa fa-info-circle"></i>
            Lengkapi data toko Anda untuk mulai berjualan
        </div>

        <form action="{{ route('seller.mypage.store-shop') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-card">
                <!-- Nama Toko -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-store"></i> Nama Toko <span class="required">*</span>
                    </label>
                    <input type="text" 
                           name="name_store" 
                           class="form-control @error('name_store') is-invalid @enderror"
                           value="{{ old('name_store', $sellerRequest->name_store ?? '') }}"
                           placeholder="Contoh: Toko Rental Kamera Jakarta"
                           required>
                    @error('name_store')
                    <div class="form-hint text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- FITUR BARU: Pencarian Alamat + Lokasi Saat Ini -->
                <div class="form-group location-section">
                    <label class="form-label">
                        <i class="fa fa-map-marker-alt"></i> Lokasi Toko <span class="required">*</span>
                    </label>

                    <!-- Tombol untuk "Lokasi Saat Ini" dan "Cari di Peta" -->
                    <div class="location-buttons">
                        <button type="button" class="btn-location" id="btnCurrentLocation" title="Gunakan lokasi saat ini">
                            <i class="fa fa-location-arrow"></i> Lokasi Saat Ini
                        </button>
                        <button type="button" class="btn-location active" id="btnSearchLocation" title="Cari lokasi dengan teks">
                            <i class="fa fa-search"></i> Cari Lokasi
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="search-input-wrapper" id="searchWrapper">
                        <input type="text" 
                               id="address_search" 
                               class="form-control"
                               placeholder="Ketik nama toko, jalan, atau lokasi...">
                        <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
                    </div>

                    <!-- Peta -->
                    <div class="map-info">
                        <i class="fa fa-info-circle"></i> Klik di peta untuk menandai lokasi atau seret marker untuk menyesuaikan
                    </div>
                    <div id="shopMap"></div>

                    <!-- Koordinat -->
                    <div class="coordinates-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Lintang <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   name="latitude" 
                                   id="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   placeholder="Contoh: -6.2088"
                                   required>
                            @error('latitude')
                            <div class="form-hint text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Bujur <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   name="longitude" 
                                   id="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   placeholder="Contoh: 106.8456"
                                   required>
                            @error('longitude')
                            <div class="form-hint text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Alamat Toko (diisi otomatis dari peta) -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-map-marker-alt"></i> Alamat Lengkap <span class="required">*</span>
                    </label>
                    <textarea name="address_store" 
                              id="address_store"
                              class="form-control @error('address_store') is-invalid @enderror"
                              placeholder="Alamat akan diisi otomatis dari peta..."
                              required>{{ old('address_store', $sellerRequest->address_store ?? '') }}</textarea>
                    @error('address_store')
                    <div class="form-hint text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Deskripsi Toko -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-align-left"></i> Deskripsi Toko
                    </label>
                    <textarea name="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Ceritakan tentang toko Anda, jenis barang yang disewakan, dll">{{ old('description', $sellerRequest->description ?? '') }}</textarea>
                    @error('description')
                    <div class="form-hint text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Logo Toko -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa fa-image"></i> Logo Toko (Opsional)
                    </label>
                    <input type="file" 
                           name="logo" 
                           class="form-control @error('logo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           id="logo-input">
                    <div class="form-hint">Format: JPG, PNG, WebP. Maksimal 2MB</div>
                    @error('logo')
                    <div class="form-hint text-danger">{{ $message }}</div>
                    @enderror
                    <div id="logo-preview"></div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa fa-check-circle"></i> Buka Toko
            </button>
        </form>
    </div>
</div>

<!-- Leaflet CSS dan JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="{{ asset('js/seller-location-picker.js') }}"></script>

<script>
    // Preview logo
    document.getElementById('logo-input').addEventListener('change', function(e) {
        const preview = document.getElementById('logo-preview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="preview-image">`;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection