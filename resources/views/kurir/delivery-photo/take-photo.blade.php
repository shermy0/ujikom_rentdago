@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left">
        <a href="{{ route('kurir.delivery-photo.index') }}" class="text-dark">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="header-center">
        <h5 class="mb-0 fw-bold">Foto Bukti Pengiriman</h5>
    </div>
</div>
@endsection

@section('navbot')
    @include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5">
    {{-- Order Info --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Detail Pesanan</h6>
            <div class="row g-2 small">
                <div class="col-4 text-muted">Kode</div>
                <div class="col-8 fw-bold">{{ $shipment->order->order_code }}</div>
                
                <div class="col-4 text-muted">Customer</div>
                <div class="col-8">{{ $shipment->order->user->name }}</div>
                
                <div class="col-4 text-muted">Produk</div>
                <div class="col-8">{{ $shipment->order->productRental->product->name ?? 'N/A' }}</div>
                
                <div class="col-4 text-muted">Alamat</div>
                <div class="col-8">{{ $shipment->order->address->full_address ?? $shipment->delivery_address_snapshot ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    {{-- Camera Section --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body text-center">
            <div class="alert alert-warning small mb-3">
                <i class="fa fa-camera me-2"></i>
                Ambil foto <strong>customer bersama paket</strong> sebagai bukti pengiriman
            </div>

            {{-- Camera Preview --}}
            <div id="cameraPreview" class="mb-3" style="display: none;">
                <video id="video" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 12px; background: #000;"></video>
                <canvas id="canvas" style="display: none;"></canvas>
            </div>

            {{-- Photo Preview --}}
            <div id="photoPreview" class="mb-3" style="display: none;">
                <img id="capturedImage" src="" alt="Preview" style="width: 100%; max-width: 400px; border-radius: 12px;">
            </div>

            {{-- File Input (Fallback) --}}
            <div id="fileInputSection">
                <label for="photoInput" class="btn btn-outline-primary w-100 py-3 rounded-3 mb-2">
                    <i class="fa fa-camera me-2"></i>Pilih/Ambil Foto
                </label>
                <input type="file" id="photoInput" accept="image/*" capture="environment" class="d-none">
            </div>

            {{-- Camera Controls --}}
            <div id="cameraControls" style="display: none;">
                <button id="captureBtn" class="btn btn-primary w-100 py-3 rounded-3 mb-2">
                    <i class="fa fa-camera me-2"></i>Ambil Foto
                </button>
                <button id="retakeBtn" class="btn btn-outline-secondary w-100 py-2 rounded-3" style="display: none;">
                    <i class="fa fa-redo me-2"></i>Ambil Ulang
                </button>
            </div>

            {{-- Submit Button --}}
            <button type="button" class="btn btn-success w-100 py-3 rounded-3 mt-2" id="submitDeliveryBtn" disabled>
                <i class="fa fa-check me-2"></i>Selesaikan & Serahkan Barang
            </button>
        </div>
    </div>
</div>

<input type="hidden" id="shipmentId" value="{{ $shipment->id }}">
@endsection

@push('scripts')
<script>
let capturedPhotoBlob = null;
let videoStream = null;

$(document).ready(function() {
    // Try to init camera automatically
    initializeCamera();

    // Photo Input Change
    $('#photoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            capturedPhotoBlob = file;
            displayPhotoPreview(file);
            $('#submitDeliveryBtn').prop('disabled', false);
        }
    });

    // Capture Button
    $('#captureBtn').on('click', function() {
        capturePhoto();
    });

    // Retake Button
    $('#retakeBtn').on('click', function() {
        retakePhoto();
    });

    // Submit Button
    $('#submitDeliveryBtn').on('click', function() {
        submitDelivery();
    });
});

function initializeCamera() {
    const video = document.getElementById('video');
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.log('Camera not available, using file input');
        $('#fileInputSection').show();
        $('#cameraControls').hide();
        return;
    }
    
    navigator.mediaDevices.getUserMedia({ 
        video: { facingMode: 'environment' },
        audio: false 
    })
    .then(function(stream) {
        videoStream = stream;
        video.srcObject = stream;
        
        $('#cameraPreview').show();
        $('#cameraControls').show();
        $('#fileInputSection').hide();
    })
    .catch(function(err) {
        console.error('Camera error:', err);
        $('#fileInputSection').show();
        $('#cameraControls').hide();
    });
}

function capturePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob(function(blob) {
        capturedPhotoBlob = blob;
        const url = URL.createObjectURL(blob);
        
        $('#capturedImage').attr('src', url);
        $('#photoPreview').show();
        $('#cameraPreview').hide();
        $('#captureBtn').hide();
        $('#retakeBtn').show();
        $('#submitDeliveryBtn').prop('disabled', false);
    }, 'image/jpeg', 0.9);
}

function retakePhoto() {
    $('#photoPreview').hide();
    $('#cameraPreview').show();
    $('#captureBtn').show();
    $('#retakeBtn').hide();
    $('#submitDeliveryBtn').prop('disabled', true);
    capturedPhotoBlob = null;
}

function displayPhotoPreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        $('#capturedImage').attr('src', e.target.result);
        $('#photoPreview').show();
        $('#fileInputSection').hide();
        
        const retakeBtn = $('<button>')
            .attr('id', 'retakeFileBtn')
            .addClass('btn btn-outline-secondary w-100 py-2 rounded-3 mt-2')
            .html('<i class="fa fa-redo me-2"></i>Pilih Foto Lain')
            .on('click', function() {
                $('#photoPreview').hide();
                $('#fileInputSection').show();
                $('#submitDeliveryBtn').prop('disabled', true);
                $('#photoInput').val('');
                $(this).remove();
                capturedPhotoBlob = null;
            });
        
        if ($('#retakeFileBtn').length === 0) {
            $('#photoPreview').after(retakeBtn);
        }
    };
    reader.readAsDataURL(file);
}

function submitDelivery() {
    if (!capturedPhotoBlob) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto Belum Diambil',
            text: 'Silakan ambil foto terlebih dahulu',
        });
        return;
    }

    const shipmentId = $('#shipmentId').val();
    const formData = new FormData();
    formData.append('shipment_id', shipmentId);
    formData.append('photo', capturedPhotoBlob, 'delivery-proof.jpg');
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: '{{ route("kurir.delivery-photo.complete") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#submitDeliveryBtn')
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Pengiriman Berhasil! 🎉',
                text: response.message,
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route("kurir.dashboard") }}';
            });
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: response?.message || 'Terjadi kesalahan',
            });
            
            $('#submitDeliveryBtn')
                .prop('disabled', false)
                .html('<i class="fa fa-check me-2"></i>Selesaikan & Serahkan Barang');
        }
    });
}
</script>
@endpush