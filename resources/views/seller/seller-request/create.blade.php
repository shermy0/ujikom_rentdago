@extends('frontend.master')
@section('navbar')
    @include('frontend.navbar')
@endsection
@section('content')
    <style>
        .seller-request-container {
            background-color: #f5f5f5;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .request-card {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .request-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        .request-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .request-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }

        .request-body {
            padding: 2rem;
        }

        .info-banner {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .info-banner i {
            color: #ffc107;
            font-size: 1.25rem;
            margin-top: 0.1rem;
        }

        .info-banner-content {
            flex: 1;
        }

        .info-banner-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 0.25rem;
        }

        .info-banner-text {
            font-size: 0.875rem;
            color: #856404;
            margin: 0;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #333;
            font-size: 1rem;
        }

        .form-label i {
            color: #ff5722;
            margin-right: 0.5rem;
        }

        .form-label .required {
            color: #dc3545;
            margin-left: 0.25rem;
        }

        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
        }

        .file-upload-wrapper:hover {
            border-color: #ff5722;
            background: #fff;
        }

        .file-upload-wrapper.has-file {
            border-color: #28a745;
            background: #f0f9f4;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .file-upload-wrapper.has-file .file-upload-icon {
            color: #28a745;
        }

        .file-upload-text {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            font-size: 0.8rem;
            color: #999;
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .preview-container {
            margin-top: 1rem;
            display: none;
        }

        .preview-container.show {
            display: block;
        }

        .preview-image {
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            border: 2px solid #ddd;
            margin: 0 auto;
            display: block;
        }

        .file-name {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #e8f5e9;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #2e7d32;
        }

        .requirements-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .requirements-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements-list li {
            font-size: 0.85rem;
            color: #666;
            padding: 0.25rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .requirements-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 87, 34, 0.4);
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
        }

        .alert-close:hover {
            opacity: 1;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
    </style>

    <div class="seller-request-container">
        <div class="request-card">
            <!-- Header -->
            <div class="request-header">
                <h2><i class="fas fa-user-tie"></i> Pengajuan Menjadi Seller</h2>
                <p>Upload KTP Anda untuk verifikasi identitas</p>
            </div>

            <div class="request-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif

                <!-- Info Banner -->
                <div class="info-banner">
                    <i class="fas fa-info-circle"></i>
                    <div class="info-banner-content">
                        <div class="info-banner-title">Proses Verifikasi</div>
                        <p class="info-banner-text">
                            Setelah pengajuan disetujui admin, Anda dapat membuka toko dan mulai berjualan
                        </p>
                    </div>
                </div>

                <form action="{{ route('seller-request.store') }}" method="POST" enctype="multipart/form-data"
                    id="seller-request-form">
                    @csrf

                    <!-- Upload KTP -->
                    <div class="form-section">
                        <label class="form-label">
                            <i class="fas fa-id-card"></i>
                            Foto KTP
                            <span class="required">*</span>
                        </label>

                        <div class="file-upload-wrapper" id="ktp-upload-wrapper">
                            <input type="file" class="file-input @error('ktp_photo') is-invalid @enderror" id="ktp_photo"
                                name="ktp_photo" accept="image/jpeg,image/png,image/jpg" required>

                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                <strong>Klik untuk upload</strong> atau drag & drop
                            </div>
                            <div class="file-upload-hint">
                                Format: JPG, JPEG, PNG (Maksimal 2MB)
                            </div>

                            <div class="file-name" id="file-name" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <span id="file-name-text"></span>
                            </div>
                        </div>

                        @error('ktp_photo')
                            <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                        @enderror

                        <!-- Preview -->
                        <div class="preview-container" id="ktp-preview">
                            <img src="" alt="Preview KTP" class="preview-image" id="ktp-preview-img">
                        </div>

                        <!-- Requirements -->
                        <div class="requirements-box">
                            <div class="requirements-title">Pastikan foto KTP Anda:</div>
                            <ul class="requirements-list">
                                <li>Tampak jelas dan tidak blur</li>
                                <li>Semua informasi dapat terbaca</li>
                                <li>Tidak ada bagian yang terpotong</li>
                                <li>Pencahayaan cukup terang</li>
                                <li>Format file JPG, JPEG, atau PNG</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="btn-group">
                        <a href="{{ route('home') }}" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                        <button type="submit" class="btn btn-submit" id="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            <span>Kirim Pengajuan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const ktpInput = document.getElementById('ktp_photo');
        const ktpWrapper = document.getElementById('ktp-upload-wrapper');
        const ktpPreview = document.getElementById('ktp-preview');
        const ktpPreviewImg = document.getElementById('ktp-preview-img');
        const fileName = document.getElementById('file-name');
        const fileNameText = document.getElementById('file-name-text');
        const submitBtn = document.getElementById('submit-btn');

        ktpInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validasi ukuran file (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB');
                    ktpInput.value = '';
                    return;
                }

                // Validasi tipe file
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG');
                    ktpInput.value = '';
                    return;
                }

                // Tampilkan nama file
                fileNameText.textContent = file.name;
                fileName.style.display = 'inline-flex';

                // Ubah tampilan wrapper
                ktpWrapper.classList.add('has-file');

                // Preview gambar
                const reader = new FileReader();
                reader.onload = function(e) {
                    ktpPreviewImg.src = e.target.result;
                    ktpPreview.classList.add('show');
                }
                reader.readAsDataURL(file);
            }
        });

        // Drag & Drop functionality
        ktpWrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            ktpWrapper.style.borderColor = '#ff5722';
            ktpWrapper.style.background = '#fff';
        });

        ktpWrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            ktpWrapper.style.borderColor = '#ddd';
            ktpWrapper.style.background = '#fafafa';
        });

        ktpWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                ktpInput.files = files;
                ktpInput.dispatchEvent(new Event('change'));
            }
            ktpWrapper.style.borderColor = '#ddd';
            ktpWrapper.style.background = '#fafafa';
        });

        // Form validation sebelum submit
        document.getElementById('seller-request-form').addEventListener('submit', function(e) {
            if (!ktpInput.files.length) {
                e.preventDefault();
                alert('Silakan upload foto KTP terlebih dahulu');
                return false;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        });
    </script>
@endsection
