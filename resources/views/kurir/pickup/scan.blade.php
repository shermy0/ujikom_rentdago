@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left">
        <a href="{{ route('kurir.orders') }}" class="text-dark">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="header-center">
        <h5 class="mb-0 fw-bold">Scan QR Pengambilan</h5>
    </div>
</div>
@endsection

@section('navbot')
@include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Detail Pesanan</h6>
            <div class="row g-2 small">
                <div class="col-4 text-muted">Kode</div>
                <div class="col-8 fw-bold">#{{ $order->order_code }}</div>

                <div class="col-4 text-muted">Toko</div>
                <div class="col-8">{{ $order->productRental->product->shop->name_store ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div id="reader" style="width: 100%; min-height: 300px; background: #000;"></div>

            <div class="p-4 text-center">
                <div class="alert alert-info small mb-0">
                    <i class="fa fa-info-circle me-2"></i>
                    Scan QR Code yang ada di <strong>dashboard seller</strong> untuk konfirmasi pengambilan paket.
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="orderId" value="{{ $order->id }}">
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const soundSuccess = new Audio('/sounds/scan-success.mp3');
const soundError   = new Audio('/sounds/scan-error.mp3');
    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning
        html5QrcodeScanner.clear();

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
                    // Start scanning again
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                });
            }
        });
    }

    function onScanFailure(error) {
        // console.warn(`Code scan error = ${error}`);
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", {
            fps: 10,
            qrbox: 250
        });
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
@endpush