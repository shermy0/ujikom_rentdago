@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left">
        <a href="{{ route('kurir.orders') }}" class="text-dark">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="header-center">
        <h5 class="mb-0 fw-bold">Konfirmasi Penyerahan</h5>
    </div>
</div>
@endsection

@section('navbot')
@include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="fa fa-map-marker-alt text-success"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Sudah Sampai di Customer</h6>
                    <small class="text-muted">Pilih metode verifikasi penyerahan barang</small>
                </div>
            </div>

            <div class="bg-light rounded-3 p-3">
                <div class="row g-2 small">
                    <div class="col-4 text-muted">Kode Order</div>
                    <div class="col-8 fw-bold">#{{ $shipment->order->order_code }}</div>

                    <div class="col-4 text-muted">Customer</div>
                    <div class="col-8">{{ $shipment->order->user->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- OTP Method -->
        <div class="col-12">
            <button class="card border-0 shadow-sm w-100 text-start p-3 hover-effect" onclick="showOtpModal()">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa fa-key text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Verifikasi Kode OTP</h6>
                        <small class="text-muted">Input 6 digit kode dari HP Customer</small>
                    </div>
                    <i class="fa fa-chevron-right ms-auto text-muted"></i>
                </div>
            </button>
        </div>

        <!-- QR Method -->
        <div class="col-12">
            <button class="card border-0 shadow-sm w-100 text-start p-3 hover-effect" onclick="startQrScan()">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa fa-qrcode text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Scan QR Customer</h6>
                        <small class="text-muted">Scan QR dari aplikasi Customer</small>
                    </div>
                    <i class="fa fa-chevron-right ms-auto text-muted"></i>
                </div>
            </button>
        </div>

        <!-- Photo Method -->
        <div class="col-12">
            <a href="{{ route('kurir.delivery-photo.show', $shipment->id) }}" class="card border-0 shadow-sm w-100 text-start p-3 text-decoration-none hover-effect">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa fa-camera text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Bukti Foto Handover</h6>
                        <small class="text-muted">Ambil foto produk bersama Customer</small>
                    </div>
                    <i class="fa fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- QR Scanner Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Scan QR Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopQrScan()"></button>
            </div>
            <div class="modal-body p-0 bg-dark position-relative">
                <div id="reader" style="width: 100%; height: 100%;"></div>
                <div class="position-absolute top-50 start-50 translate-middle border border-white border-4 rounded-3" style="width: 250px; height: 250px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-effect:active {
        background-color: #f8f9fa;
        transform: scale(0.98);
        transition: transform 0.1s;
    }
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrScanner = null;

    function showOtpModal() {
        Swal.fire({
            title: 'Verifikasi OTP',
            text: 'Masukkan 6 digit kode OTP dari HP Customer',
            input: 'text',
            inputAttributes: {
                maxlength: 6,
                autocapitalize: 'off',
                autocorrect: 'off',
                style: 'text-align: center; letter-spacing: 1rem; font-size: 1.5rem;'
            },
            showCancelButton: true,
            confirmButtonText: 'Verifikasi',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: (otp) => {
                if (!otp || otp.length !== 6) {
                    Swal.showValidationMessage('Masukkan 6 digit OTP');
                    return false;
                }
                return $.ajax({
                    url: "{{ route('kurir.handover.verify-otp') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        shipment_id: "{{ $shipment->id }}",
                        otp: otp
                    }
                }).catch(error => {
                    Swal.showValidationMessage(error.responseJSON ? error.responseJSON.message : "Gagal verifikasi OTP");
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.value.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = result.value.redirect;
                });
            }
        });
    }

    function startQrScan() {
        $('#qrModal').modal('show');
        if (!html5QrScanner) {
            html5QrScanner = new Html5Qrcode("reader");
        }

        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        };
        html5QrScanner.start({
            facingMode: "environment"
        }, config, onScanSuccess);
    }

    function stopQrScan() {
        if (html5QrScanner) {
            html5QrScanner.stop().catch(err => console.error("Error stopping scanner", err));
        }
    }

    function onScanSuccess(decodedText) {
        stopQrScan();
        $('#qrModal').modal('hide');

        Swal.showLoading();

        $.ajax({
            url: "{{ route('kurir.handover.verify-qr') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                shipment_id: "{{ $shipment->id }}",
                order_code: decodedText
            },
            success: function(response) {
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
                const message = xhr.responseJSON ? xhr.responseJSON.message : "QR Code tidak valid";
                Swal.fire({
                    icon: 'error',
                    title: 'Verifikasi Gagal',
                    text: message
                });
            }
        });
    }
</script>
@endpush