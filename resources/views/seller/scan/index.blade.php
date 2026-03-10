@extends('frontend.masterseller')

@section('content')
    @include('seller.scan.styles')

    <div class="scan-container">
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.dashboard.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Scan QR Pickup
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="scan-content">

    {{-- Tabs --}}
    <div class="scan-tabs">
        <button class="tab-button active" data-target="tab-qr">Scan QR</button>
        <button class="tab-button" data-target="tab-manual">Input Manual</button>
    </div>

    {{-- Tab Contents --}}
    <div id="tab-qr" class="tab-content active">
        <div class="camera-container">
            <div class="camera-wrapper">
                <div id="preview"></div>
                <div class="camera-overlay">
                    <div class="camera-corner top-left"></div>
                    <div class="camera-corner top-right"></div>
                    <div class="camera-corner bottom-left"></div>
                    <div class="camera-corner bottom-right"></div>
                    <div class="scan-laser"></div>
                </div>
                <div class="scan-instruction-overlay">
                    <i class="fa fa-qrcode"></i> Arahkan QR Code ke kamera
                </div>
                <button id="retryButton" class="retry-button-overlay" onclick="initScanner()">
                    <i class="fa fa-camera"></i> Buka Kamera
                </button>
            </div>
        </div>
    </div>

    <div id="tab-manual" class="tab-content">
        <div class="manual-input-card">
            <div class="manual-input-header">
                <div class="manual-input-icon">
                    <i class="fa fa-keyboard"></i>
                </div>
                <div class="manual-input-title">Input Kode Manual</div>
            </div>
            <div class="input-group">
                <input type="text" id="manualCodeInput" class="manual-code-input"
                    placeholder="Masukkan kode order (contoh: ORD-20250106-XXX)" autocomplete="off">
                <button id="btnVerifyManual" class="btn-verify-manual" onclick="verifyManualCode()">
                    <i class="fa fa-check"></i>
                    <span>Cek</span>
                </button>
            </div>
        </div>
    </div>

</div>

    </div>

        {{-- ===========================
         INPUT KAMERA BUKTI SERAH TERIMA
         =========================== --}}
    <input
        type="file"
        id="handoverCamera"
        accept="image/*"
        capture="environment"
        style="display:none"
    />

    <!-- Script eksternal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

    @include('seller.scan.scripts')
    
@endsection