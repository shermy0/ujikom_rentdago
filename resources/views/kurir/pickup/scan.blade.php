@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header scan-header">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <a href="{{ route('kurir.orders') }}" class="scan-back-btn" aria-label="Kembali">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-bold scan-title">Scan QR Pengambilan</h5>
        <span></span>
    </div>
</div>
@endsection

@section('navbot')
@include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5 pt-3">
    @php
        $shop = $order->productRental->product->shop ?? null;
        $pickupAddress = $shop->address_store
            ?? $order->pickup_address
            ?? 'Alamat toko belum diatur';
    @endphp
    <div class="card border-0 shadow-sm mb-3 scan-detail-card">
        <div class="card-body">
            <h6 class="fw-bold mb-3 d-flex align-items-center">
                <i class="fa fa-receipt me-2 text-success"></i>Detail Pesanan
            </h6>
            <div class="scan-info-grid">
                <div class="scan-label">Kode</div>
                <div class="scan-value fw-bold">{{ $order->order_code }}</div>

                <div class="scan-label">Customer</div>
                <div class="scan-value">{{ $order->user->name ?? 'N/A' }}</div>

                <div class="scan-label">Produk</div>
                <div class="scan-value">{{ $order->productRental->product->name ?? 'N/A' }}</div>

                <div class="scan-label">Alamat Pickup</div>
                <div class="scan-value scan-address">{{ $pickupAddress }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden scan-reader-card">
        <div class="card-body p-0">
            <div class="scan-reader-wrap">
                <div id="reader" class="scan-reader-box"></div>
                <button id="startScanBtn" type="button" class="btn btn-success scan-start-btn">
                    Aktifkan Kamera
                </button>
            </div>

            <div class="p-3 p-md-4 text-center">
                <div class="alert alert-info small mb-0 scan-info-alert">
                    <i class="fa fa-info-circle me-2"></i>
                    Arahkan kamera ke QR Code di <strong>dashboard seller</strong> untuk konfirmasi pengambilan.
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="orderId" value="{{ $order->id }}">
@endsection

@push('styles')
<style>
    .scan-header {
        padding: 12px 14px;
    }

    .scan-title {
        font-size: 18px;
        color: #0f172a;
        flex: 1;
        margin-left: 10px;
    }

    .scan-back-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: rgba(255, 255, 255, 0.9);
        color: #111827;
    }

    .scan-detail-card,
    .scan-reader-card {
        border-radius: 10px;
    }

    .scan-info-grid {
        display: grid;
        grid-template-columns: 90px 1fr;
        row-gap: 8px;
        column-gap: 10px;
        font-size: 13px;
    }

    .scan-label {
        color: #6b7280;
        font-weight: 500;
    }

    .scan-value {
        color: #111827;
        overflow-wrap: anywhere;
    }

    .scan-address {
        line-height: 1.45;
    }

    .scan-reader-wrap {
        padding: 12px;
        background: #f8fafc;
    }

    .scan-reader-box {
        border-radius: 10px;
        overflow: hidden !important;
        min-height: 250px;
        background: #000;
    }

    .scan-start-btn {
        margin-top: 10px;
        width: 100%;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 12px;
    }

    .scan-info-alert {
        border: 0;
        border-radius: 12px;
    }

    #reader video {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const soundSuccess = new Audio('/sounds/scan-success.mp3');
const soundError   = new Audio('/sounds/scan-error.mp3');
let html5QrCode = null;
let isScanning = false;

function initScanner() {
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }
}

function startScanner() {
    if (isScanning) return;

    initScanner();
    const startBtn = document.getElementById('startScanBtn');
    if (startBtn) {
        startBtn.disabled = true;
        startBtn.textContent = 'Membuka kamera...';
    }

    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 220, height: 220 },
            aspectRatio: 1
        },
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        if (startBtn) {
            startBtn.style.display = 'none';
        }
    }).catch((error) => {
        if (startBtn) {
            startBtn.disabled = false;
            startBtn.textContent = 'Aktifkan Kamera';
        }
        Swal.fire({
            icon: 'error',
            title: 'Kamera tidak tersedia',
            text: 'Mohon izinkan akses kamera untuk scan QR.'
        });
        console.error(error);
    });
}

function stopScanner() {
    if (!html5QrCode || !isScanning) return Promise.resolve();
    return html5QrCode.stop().then(() => {
        isScanning = false;
    }).catch(() => {
        isScanning = false;
    });
}

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning
        stopScanner();

        // Use browser geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                verifyPickup(decodedText, position.coords.latitude, position.coords.longitude);
            }, function(error) {
                console.warn("Geolocation error: " + error.message);
                verifyPickup(decodedText, null, null);
            });
        } else {
            verifyPickup(decodedText, null, null);
        }
    }

    function verifyPickup(code, lat, lng) {
        const orderId = document.getElementById('orderId').value;

        // Show loading
        Swal.fire({
            title: 'Memverifikasi...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "{{ route('kurir.pickup.verify') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                order_id: orderId,
                order_code: code,
                lat: lat,
                lng: lng
            },
            success: function(response) {
                // 🔊 pickup sukses
                soundSuccess.currentTime = 0;
                soundSuccess.play().catch(()=>{});

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect;
                });
            },
            error: function(xhr) {
                // 🔊 pickup gagal
                soundError.currentTime = 0;
                soundError.play().catch(()=>{});

                const message = xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan";
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                }).then(() => {
                    const startBtn = document.getElementById('startScanBtn');
                    if (startBtn) {
                        startBtn.style.display = 'block';
                        startBtn.disabled = false;
                        startBtn.textContent = 'Aktifkan Kamera';
                    }
                    startScanner();
                });
            }
        });
    }

    function onScanFailure(error) {
        // console.warn(`Code scan error = ${error}`);
    }

    document.getElementById('startScanBtn')?.addEventListener('click', startScanner);
</script>
@endpush