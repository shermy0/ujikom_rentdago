@extends('frontend.masterseller')

@section('content')
@include('seller.scan.styles')

<div class="scan-container">
    {{-- HEADER --}}
    <div class="scan-header-bar">
        <div class="scan-header-back">
            <a href="{{ route('seller.scan.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="scan-header-title">Foto Bukti Serah Barang</div>
        <div style="width:40px;"></div>
    </div>

    {{-- CONTENT --}}
    <div class="scan-content">

        <div class="handover-card">
            <div class="handover-info">
                <p style="font-size:14px; color:#666; margin-bottom:8px;">
                    Ambil foto <strong>customer, barang sewaan, dan KTP sebagai jaminan sewa</strong>.
                    Foto ini wajib sebelum rental dimulai.
                </p>
            </div>

            {{-- PREVIEW FOTO --}}
            <div class="handover-preview">
                <img id="photoPreview" src="" alt="Preview Foto" style="display:none;">
                <div id="photoPlaceholder">
                    <i class="fa fa-camera"></i>
                    <p>Belum ada foto</p>
                </div>
            </div>

            {{-- BUTTON --}}
            <div class="handover-actions">
                <button class="btn btn-secondary" onclick="openCamera()">
                    <i class="fa fa-camera"></i> Ambil Foto
                </button>

                <button id="btnUpload" class="btn btn-success" onclick="uploadPhoto()" disabled>
                    <i class="fa fa-upload"></i> Simpan & Lanjutkan
                </button>
            </div>
        </div>

    </div>
</div>

{{-- INPUT CAMERA --}}
<input
    type="file"
    id="handoverCamera"
    accept="image/*"
    capture="environment"
    style="display:none"
/>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const uploadRoute = "{{ route('seller.scan.uploadStartProof') }}";
    const orderId = new URLSearchParams(window.location.search).get('order_id');

    let selectedFile = null;

    function openCamera() {
        document.getElementById('handoverCamera').click();
    }

    document.getElementById('handoverCamera').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        selectedFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
            document.getElementById('photoPlaceholder').style.display = 'none';
            document.getElementById('btnUpload').disabled = false;
        };
        reader.readAsDataURL(file);
    });

function uploadPhoto() {
    if (!selectedFile || !orderId) {
        Swal.fire('Error', 'Foto atau order tidak valid', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('photo', selectedFile);

    Swal.fire({
        title: 'Mengunggah foto...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // ============================
    // STEP 1 — UPLOAD FOTO SERAH BARANG
    // ============================
    fetch(uploadRoute, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(uploadRes => {

        if (!uploadRes.success) {
            Swal.fire('Gagal', uploadRes.message, 'error');
            return;
        }

        // ============================
        // STEP 2 — START RENTAL
        // confirmed → ongoing
        // ============================
        return fetch("{{ route('seller.scan.start') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId
            })
        });
    })
    .then(res => res.json())
    .then(startRes => {

        if (!startRes.success) {
            Swal.fire('Gagal', startRes.message, 'error');
            return;
        }

        // ============================
        // SUCCESS FINAL
        // ============================
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Rental dimulai, status menjadi ongoing'
        }).then(() => {
            window.location.href = "{{ route('seller.scan.index') }}";
        });

    })
    .catch(() => {
        Swal.fire('Error', 'Terjadi kesalahan saat memproses data', 'error');
    });
}
</script>

<style>
.handover-card {
    background: #fff;
    padding: 16px;
    border-radius: 12px;
}

.handover-preview {
    margin: 16px 0;
    border-radius: 12px;
    border: 2px dashed #ddd;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.handover-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#photoPlaceholder {
    text-align: center;
    color: #aaa;
}

.handover-actions {
    display: flex;
    gap: 10px;
}

.handover-actions button {
    flex: 1;
}
</style>
@endsection
