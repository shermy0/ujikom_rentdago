@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/address-customer.css') }}?v={{ time() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
    .address-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding-bottom: 2rem;
    }

    .address-header-bar {
        background: linear-gradient(135deg, #ee4d2d 0%, #ff6b35 100%);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 20px rgba(238, 77, 45, 0.3);
    }

    .address-header-bar a {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.3s;
        backdrop-filter: blur(10px);
    }

    .address-header-bar a:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateX(-3px);
    }

    .address-header-bar .title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .header-spacer {
        width: 40px;
    }

    .address-form-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 0 1rem;
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
        border-color: #ee4d2d;
        box-shadow: 0 0 0 3px rgba(238, 77, 45, 0.1);
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

    .location-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .btn-location {
        flex: 1;
        padding: 0.75rem;
        border: 2px solid #ee4d2d;
        background: #fff;
        color: #ee4d2d;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
    }

    .btn-location:hover {
        background: #ee4d2d;
        color: #fff;
    }

    .btn-location.active {
        background: #ee4d2d;
        color: #fff;
    }

    #map {
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

    .search-input-wrapper {
        position: relative;
        margin-bottom: 1rem;
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

    .btn-save {
        width: 100%;
        background: linear-gradient(135deg, #ee4d2d 0%, #d63a1e 100%);
        color: #fff;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(238, 77, 45, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-current-location {
        background-color: #ee4d2d;
        color: white;
        border: none;
    }
</style>

<div class="address-container">
    {{-- HEADER --}}
    <div class="address-header-bar">
        <a href="{{ route('customer.addresses.index') }}">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="title">Tambah Alamat</div>
        <div class="header-spacer"></div>
    </div>

    <div class="address-form-container">
        {{-- ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="form-card">
            <form action="{{ route('customer.addresses.store') }}" method="POST">
                @csrf

                {{-- LABEL --}}
                <div class="form-group">
                    <label class="form-label">Label Alamat <span class="required">*</span></label>
                    <input type="text"
                           name="label"
                           class="form-control"
                           placeholder="Contoh: Rumah, Kantor, Kos"
                           value="{{ old('label') }}"
                           required>
                </div>

                {{-- NAMA PENERIMA --}}
                <div class="form-group">
                    <label class="form-label">Nama Penerima <span class="required">*</span></label>
                    <input type="text"
                           name="receiver_name"
                           class="form-control"
                           placeholder="Nama orang yang menerima barang"
                           value="{{ old('receiver_name', auth()->user()->name) }}"
                           required>
                </div>

                {{-- NO HP --}}
                <div class="form-group">
                    <label class="form-label">No. Telepon Penerima <span class="required">*</span></label>
                    <input type="text"
                           name="receiver_phone"
                           class="form-control"
                           placeholder="08xxxxxxxxxx"
                           value="{{ old('receiver_phone', auth()->user()->phone) }}"
                           required>
                </div>

                {{-- LOCATION SECTION --}}
                <div class="form-group">
                    <label class="form-label"><i class="fa fa-map-marker-alt"></i> Lokasi <span class="required">*</span></label>

                    {{-- Tombol untuk "Lokasi Saat Ini" dan "Cari Lokasi" --}}
                    <div class="location-buttons">
                        <button type="button" class="btn-location" id="btnCurrentLocation" title="Gunakan lokasi saat ini">
                            <i class="fa fa-location-arrow"></i> Lokasi Saat Ini
                        </button>
                        <button type="button" class="btn-location active" id="btnSearchLocation" title="Cari lokasi dengan teks">
                            <i class="fa fa-search"></i> Cari Lokasi
                        </button>
                    </div>

                    {{-- Search Input --}}
                    <div class="search-input-wrapper" id="searchWrapper">
                        <input type="text" 
                               id="address_search" 
                               class="form-control"
                               placeholder="Ketik nama jalan, lokasi, atau alamat...">
                        <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
                    </div>

                    {{-- Peta --}}
                    <div class="map-info">
                        <i class="fa fa-info-circle"></i> Klik di peta untuk menandai lokasi atau seret marker untuk menyesuaikan
                    </div>
                    <div id="map" style="height: 350px; width: 100%; border-radius: 8px; overflow: hidden; border: 2px solid #ddd; margin-bottom: 1rem; display: block;"></div>

                    {{-- Koordinat --}}
                    <div class="coordinates-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Lintang <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   name="latitude" 
                                   id="latitude"
                                   class="form-control"
                                   placeholder="Contoh: -6.2088"
                                   required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Bujur  <span class="required">*</span></label>
                            <input type="number" 
                                   step="0.00000001"
                                   name="longitude" 
                                   id="longitude"
                                   class="form-control"
                                   placeholder="Contoh: 106.8456"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ALAMAT LENGKAP --}}
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                    <textarea name="address"
                              rows="4"
                              id="addressInput"
                              class="form-control"
                              placeholder="Alamat akan diisi otomatis dari peta..."
                              required>{{ old('address') }}</textarea>
                </div>

                {{-- CATATAN --}}
                <div class="form-group">
                    <label class="form-label">Catatan (Opsional)</label>
                    <input type="text"
                           name="notes"
                           class="form-control"
                           placeholder="Contoh: Rumah pagar hitam"
                           value="{{ old('notes') }}">
                </div>

                {{-- SUBMIT --}}
                <button type="submit" class="btn-save w-100 text-white" id="btnSave">
                    <span class="btn-text">Simpan Alamat</span>
                    <span class="btn-spinner" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i> Memproses...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet CSS dan JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="{{ asset('js/customer-location-picker.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const btnSave = document.getElementById('btnSave');
    if (!form || !btnSave) return;
    
    const btnText = btnSave.querySelector('.btn-text');
    const btnSpinner = btnSave.querySelector('.btn-spinner');

    form.addEventListener('submit', function () {
        // disable tombol
        btnSave.disabled = true;

        // tampilkan spinner, sembunyikan text
        if (btnText && btnSpinner) {
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline-block';
        }
    });
});
</script>

@endsection
