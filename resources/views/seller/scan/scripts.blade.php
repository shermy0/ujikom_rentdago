<script>
    // Definisi routes
    const verifyRoute = '{{ route('seller.scan.verify') }}';
    const startRoute = '{{ route('seller.scan.start') }}';
    const returnRoute = '{{ route('seller.scan.return') }}';
const handoverProofRoute = "{{ route('seller.scan.handover-proof') }}";

// ===========================
// SOUND EFFECTS
// ===========================
const soundSuccess = new Audio('/sounds/scan-success.mp3');
const soundError   = new Audio('/sounds/scan-error.mp3');
const soundWarn    = new Audio('/sounds/scan-warning.mp3');

// biar ga delay di mobile
[soundSuccess, soundError, soundWarn].forEach(a => {
    a.preload = 'auto';
});

    // Variables
    let html5QrcodeScanner = null;
let isProcessing = false; // untuk QR
let isManualProcessing = false; // untuk manual
    let currentOrderId = null;

    // ===========================
    // HELPER FUNCTIONS
    // ===========================

    function showAlert(type, msg) {
        const el = document.getElementById(`alert${type}`);
        el.textContent = msg;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 4000);
    }

    function showSwal(type, message, options = {}) {
    return Swal.fire({
        width: '300px',
        icon: type, // success | error | warning | info
        text: message,
        confirmButtonColor:
            type === 'success' ? '#28a745'
            : type === 'error' ? '#dc3545'
            : type === 'warning' ? '#ffc107'
            : '#3fc3ee',
        confirmButtonText: 'OK',
        ...options
    });
}


function resetScan() {
    currentOrderId = null;
    isProcessing = false;
    isManualProcessing = false;

    // resume kamera hanya kalau QR tab aktif
    const qrTabActive = document.getElementById('tab-qr').classList.contains('active');

    if (qrTabActive && html5QrcodeScanner) {
        html5QrcodeScanner.resume();
    }
}


    // ===========================
    // CAMERA SCANNER
    // ===========================

    function initScanner() {
        document.getElementById('retryButton').style.display = 'none';

        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(err => console.error('Clear error:', err));
        }

        html5QrcodeScanner = new Html5Qrcode("preview");

        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            aspectRatio: 1.0
        };

        html5QrcodeScanner.start({
                facingMode: "environment"
            },
            config,
            onScanSuccess,
            (errorMessage) => {
                /* Ignore frame errors */ }
        ).catch(err => {
            console.error('Camera error:', err);
            document.getElementById('retryButton').style.display = 'block';
            showAlert('Danger', 'Gagal membuka kamera. Pastikan izin diberikan.');
        });
    }

function onScanSuccess(decodedText) {
    if (isProcessing) return;

    isProcessing = true;

    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause();
    }

    verifyOrderCode(decodedText, 'qr');
}


    // ===========================
    // STEP 1: VERIFY ORDER
    // ===========================

function verifyOrderCode(orderCode, source = 'qr') {
    fetch(verifyRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ order_code: orderCode })
    })
    .then(res => res.json())
.then(data => {
    if (data.success) {

        // 🔊 SUCCESS: QR / kode VALID
        soundSuccess.currentTime = 0;
        soundSuccess.play().catch(() => {});

        showOrderDetail(data.data);
        document.getElementById('manualCodeInput').value = '';

    } else {
        handleVerifyError(data);
    }
})

    .catch(err => {
        console.error('Verify error:', err);
        showAlert('Danger', 'Terjadi kesalahan koneksi');
    })
    .finally(() => {
        // 🔥 RESET STATE SESUAI MODE
        if (source === 'manual') {
            isManualProcessing = false;
            document.getElementById('btnVerifyManual').disabled = false;
        } else {
            isProcessing = false;
        }
    });
}

function handleVerifyError(data) {
    // 🔊 bunyi error
    soundError.currentTime = 0;
    soundError.play().catch(() => {});

    if (data.too_early && data.time_remaining && data.scheduled_time) {
        showEarlyPickupAlert(data);
    } else {
        showSwal('error', data.message)
            .then(resetScan);
    }
}



    function showEarlyPickupAlert(data) {
        // 🔊 bunyi warning
    soundWarn.currentTime = 0;
    soundWarn.play().catch(() => {});
        Swal.fire({
            width: '340px',
            icon: 'warning',
            iconColor: '#ffc107',
            title: 'Belum Waktunya Pickup',
            html: `
                <div style="text-align: center; font-size: 0.9rem; line-height: 1.6;">
                    <p style="margin-bottom: 12px; color: #666;">Barang ini bisa diambil pada:</p>
                    <div style="background: #fff3cd; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                        <i class="fa fa-clock" style="color: #856404; margin-right: 6px;"></i>
                        <strong style="color: #856404; font-size: 1rem;">${data.scheduled_time}</strong>
                    </div>
                    <p style="color: #666; font-size: 0.85rem;">
                        <i class="fa fa-hourglass-half"></i>
                        Waktu tersisa: <strong>${data.time_remaining}</strong>
                    </p>
                </div>
            `,
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#ff6b35',
            customClass: {
                confirmButton: 'small-swal-button'
            }
        }).then(() => resetScan());
    }

    // ===========================
    // STEP 2: SHOW ORDER DETAIL
    // ===========================

    function showOrderDetail(order) {
        currentOrderId = order.order_id;
        const actionType = order.action_type;

        let statusText, actionBtnText, actionColor;

        if (actionType === 'return') {
            statusText = 'Sedang Disewa';
            actionBtnText = 'Terima Pengembalian';
            actionColor = '#ff6b35';
        } else {
            statusText = 'Siap Diambil';
            actionBtnText = 'Serahkan Barang';
            actionColor = '#28a745';
        }

        // Build HTML untuk scheduled time (hanya tampil jika ada)
        let scheduledTimeHtml = '';
        if (order.scheduled_start_time && order.scheduled_start_time !== '-') {
            scheduledTimeHtml =
                `<p style="margin-bottom: 4px;"><strong>Jadwal Pickup:</strong> ${order.scheduled_start_time}</p>`;
        }

        // Build HTML untuk end time (hanya tampil untuk return)
        let endTimeHtml = '';
        if (actionType === 'return' && order.end_time && order.end_time !== '-') {
            endTimeHtml = `<p style="margin-bottom: 4px;"><strong>Batas Waktu:</strong> ${order.end_time}</p>`;
        }

        Swal.fire({
            width: '320px',
            padding: '1em',
            title: `<span style="font-size: 1rem">${order.product_name}</span>`,
html: `
    <div style="text-align:center; margin-bottom:10px;">
        <img 
            src="${order.product_image}" 
            alt="Foto Barang"
            style="
                width: 100%;
                max-height: 160px;
                object-fit: cover;
                border-radius: 10px;
                border: 1px solid #eee;
            "
        >
    </div>

    <div style="text-align: left; font-size: 0.85rem; line-height: 1.4;">
        <p style="margin-bottom: 4px;"><strong>Kode:</strong> #${order.order_code}</p>
        <p style="margin-bottom: 4px;"><strong>Penyewa:</strong> ${order.customer_name}</p>
        <p style="margin-bottom: 4px;">
            <strong>Status:</strong>
            <span class="status-badge ${order.status}">
                ${statusText}
            </span>
        </p>
        ${scheduledTimeHtml}
        ${endTimeHtml}
    </div>
`,

            icon: 'info',
            iconColor: '#3fc3ee',
            showCancelButton: true,
            confirmButtonText: `<i class="fa fa-check"></i> ${actionBtnText}`,
            cancelButtonText: 'Batal',
            confirmButtonColor: actionColor,
            cancelButtonColor: '#f1f3f5',
            reverseButtons: true,
            allowOutsideClick: false,
            customClass: {
                confirmButton: 'small-swal-button',
                cancelButton: 'small-swal-button'
            }
        }).then((result) => {
if (result.isConfirmed) {
if (actionType === 'start') {
    openHandoverCamera();
} else {
    processOrder(actionType);
}

}


        });
    }

    function ensureCameraReady() {
    return new Promise((resolve, reject) => {

        let video = getQrVideoElement();
        if (video && video.videoWidth > 0) {
            return resolve(video);
        }

        // Kalau scanner belum ada → init
        if (!html5QrcodeScanner) {
            initScanner();
        } else {
            html5QrcodeScanner.resume();
        }

        // Tunggu max 3 detik sampai video ready
        let attempts = 0;
        const timer = setInterval(() => {
            video = getQrVideoElement();

            if (video && video.videoWidth > 0) {
                clearInterval(timer);
                resolve(video);
            }

            attempts++;
            if (attempts > 30) {
                clearInterval(timer);
                reject('Kamera gagal diaktifkan');
            }
        }, 100);
    });
}


function openHandoverCamera() {
    Swal.fire({
        width: '340px',
        title: 'Foto Serah Barang',
        html: `
            <div style="margin-bottom:10px;">
                <video id="handoverVideo"
                       autoplay
                       playsinline
                       style="width:100%; border-radius:10px;"></video>
            </div>
            <p style="font-size:0.8rem;color:#666;">
                Ambil foto <b>customer, barang sewaan, dan KTP</b>
            </p>
        `,
        showCancelButton: true,
        confirmButtonText: '📸 Ambil Foto',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        allowOutsideClick: false,

        didOpen: () => startHandoverCamera(),

        /** 🔥 KUNCI UTAMA ADA DI SINI */
        preConfirm: () => {
            return captureHandoverPhoto(); 
        },

        willClose: () => stopHandoverCamera()
    }).then(result => {
        if (!result.isConfirmed) {
            resetScan();
        }
    });
}

let handoverStream = null;

function startHandoverCamera() {
    // 🔥 BENAR-BENAR LEPAS KAMERA QR
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().catch(() => {});
        html5QrcodeScanner = null;
    }

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' },
        audio: false
    })
    .then(stream => {
        handoverStream = stream;
        const video = document.getElementById('handoverVideo');
        video.srcObject = stream;

        // 🔥 WAJIB
        video.onloadedmetadata = () => {
            video.play();
        };
    })
    .catch(() => {
        showSwal('error', 'Gagal membuka kamera');
        resetScan();
    });
}

function stopHandoverCamera() {
    if (handoverStream) {
        handoverStream.getTracks().forEach(t => t.stop());
        handoverStream = null;
    }
}

function captureHandoverPhoto() {
    return new Promise((resolve, reject) => {
        const video = document.getElementById('handoverVideo');

        if (!video || video.videoWidth === 0) {
            showSwal('error', 'Kamera belum siap');
            reject();
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(blob => {
            if (!blob) {
                showSwal('error', 'Gagal mengambil foto');
                reject();
                return;
            }

            // 🔥 LANJUT KE PREVIEW
            confirmAndUploadSnapshot(blob, canvas.toDataURL('image/jpeg'));
            resolve();
        }, 'image/jpeg', 0.9);
    });
}

function takeSnapshotFromVideo(video) {
    const canvas = document.createElement('canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(blob => {
        if (!blob) {
            showSwal('error', 'Gagal mengambil foto');
            resetScan();
            return;
        }

        confirmAndUploadSnapshot(blob, canvas.toDataURL('image/jpeg'));
    }, 'image/jpeg', 0.9);
}

function confirmAndUploadSnapshot(blob, previewBase64) {
    Swal.fire({
        width: '320px',
        title: 'Foto Serah Barang',
        html: `
            <img src="${previewBase64}"
                 style="width:100%; border-radius:10px; margin-bottom:10px;">
            <p style="font-size:0.85rem;color:#666;">
                Pastikan barang terlihat jelas
            </p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Gunakan Foto',
        cancelButtonText: 'Ambil Ulang',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#ffc107'
    }).then(result => {
        if (result.isConfirmed) {
            uploadHandoverSnapshot(blob);
        } else {
            // resume kamera QR
resetScan();
        }
    });
}

function uploadHandoverSnapshot(blob) {
    Swal.fire({
        width: '300px',
        title: 'Menyimpan foto...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const formData = new FormData();
    formData.append('order_id', currentOrderId);
    formData.append('photo', blob, 'handover.jpg');

    fetch("{{ route('seller.scan.uploadStartProof') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) throw data;

        // FOTO OK → START ORDER
        processOrder('start');
    })
    .catch(err => {
        Swal.fire({
            width: '300px',
            icon: 'error',
            title: 'Gagal',
            text: err.message || 'Upload gagal'
        }).then(resetScan);
    });
}


function getQrVideoElement() {
    return document.querySelector('#preview video');
}




    // ===========================
    // STEP 3: PROCESS ACTION
    // ===========================

    function processOrder(actionType) {
        const url = actionType === 'start' ? startRoute : returnRoute;

        if (!url) {
            console.error('URL tidak ditemukan untuk aksi:', actionType);
            Swal.fire({
                width: '300px',
                icon: 'error',
                title: 'Error',
                text: 'Konfigurasi route tidak valid'
            }).then(resetScan);
            return;
        }

        // Show loading
        Swal.fire({
            width: '300px',
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    order_id: currentOrderId
                })
            })
            .then(res => {
                // Handle non-OK responses
                if (!res.ok) {
                    return res.json().then(errorData => {
                        throw errorData;
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessAlert(data.message);
                } else {
                    handleProcessError(data);
                }
            })
            .catch(err => {
                console.error('Process error:', err);
                handleProcessError(err);
            });
    }

    function handleProcessError(err) {
        // Cek jika error karena terlalu cepat (too_early flag)
        if (err.too_early && err.time_remaining && err.scheduled_time) {
            showEarlyPickupAlert(err);
        } else {
            // Error umum lainnya
            const errMsg = err.message || 'Terjadi kesalahan sistem';
            Swal.fire({
                width: '300px',
                icon: 'error',
                title: 'Gagal',
                text: errMsg,
                confirmButtonColor: '#dc3545'
            }).then(resetScan);
        }
    }

function showSuccessAlert(message) {
    // 🔊 sukses final

    showSwal('success', message)
        .then(resetScan);
}


    // ===========================
    // MANUAL INPUT
    // ===========================

function verifyManualCode() {
    const input = document.getElementById('manualCodeInput');
    const orderCode = input.value.trim();

    if (!orderCode) {
        showAlert('Danger', 'Masukkan kode order terlebih dahulu');
        return;
    }

    if (isManualProcessing) return;

    isManualProcessing = true;
    const btn = document.getElementById('btnVerifyManual');
    btn.disabled = true;

    verifyOrderCode(orderCode, 'manual');
}

    // ===========================
    // INITIALIZATION
    // ===========================

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('manualCodeInput');
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyManualCode();
        }
    });

    // 🚀 AUTO START KAMERA JIKA TAB QR AKTIF
    const qrTabActive = document.getElementById('tab-qr')?.classList.contains('active');
    if (qrTabActive) {
        initScanner();
    }
});


    // Tab switch
document.querySelectorAll('.tab-button').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active dari semua button & content
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        // Aktifkan yang dipilih
        this.classList.add('active');
        const target = this.getAttribute('data-target');
        document.getElementById(target).classList.add('active');

        // Kalau pindah ke QR tab, start kamera
        if(target === 'tab-qr') {
            initScanner();
        } else {
            // Kalau pindah ke manual, pause kamera
            if(html5QrcodeScanner) html5QrcodeScanner.pause();
        }
    });
});

</script>