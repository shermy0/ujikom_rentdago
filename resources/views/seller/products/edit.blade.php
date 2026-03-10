@extends('frontend.masterseller')

@section('content')
    <style>
        .edit-product-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
        }

        .edit-header-bar {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .edit-header-back {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-header-back a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
        }

        .edit-header-title {
            flex: 1;
            text-align: center;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .edit-header-spacer {
            width: 40px;
        }

        .form-section {
            padding: 1rem;
        }

        .form-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .required {
            color: #dc3545;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #ff5722;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-hint {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .invalid-feedback {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.8rem;
            color: #dc3545;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }

        .images-section-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-count {
            background: #ff5722;
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .existing-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .existing-image-item {
            position: relative;
            padding-top: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
        }

        .existing-image-item img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .delete-image-btn {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(220, 53, 69, 0.95);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .delete-image-btn:hover {
            background: #dc3545;
            transform: scale(1.1);
        }

        .image-upload-section {
            text-align: center;
            padding: 2rem 1.5rem;
            border: 2px dashed #ccc;
            border-radius: 12px;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            width: 100%;
        }

        .image-upload-section:hover {
            border-color: #ff5722;
            background: #fff5f2;
        }

        .image-upload-section .upload-icon {
            font-size: 3.5rem;
            color: #9e9e9e;
            margin-bottom: 1rem;
            display: block;
        }

        .image-upload-section .upload-text {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1rem;
            font-weight: 600;
            display: block;
        }

        .image-upload-section .upload-hint {
            display: block;
            margin: 0;
            font-size: 0.8rem;
            color: #757575;
            line-height: 1.5;
        }

        .new-image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .new-image-item {
            position: relative;
            padding-top: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            border: 2px solid #28a745;
        }

        .new-image-item img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .new-badge {
            position: absolute;
            top: 0.25rem;
            left: 0.25rem;
            background: #28a745;
            color: white;
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 600;
        }

        .remove-new-image {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(220, 53, 69, 0.95);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            background: #007bff;
            border: none;
            color: #fff;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 3rem;
        }

        .submit-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .error-alert {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .success-alert {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .code-display {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #007bff;
            font-weight: 500;
            font-family: monospace;
        }

        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 1.5rem 0;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.875rem;
            color: #004085;
            line-height: 1.5;
        }

        /* Additional fixes for upload section */
        .image-upload-wrapper {
            margin-bottom: 1rem;
        }

        #new-image-preview:empty {
            display: none;
        }

        #new-image-preview:not(:empty) {
            margin-top: 1rem;
        }
    </style>

    <div class="edit-product-container">
        <!-- Header -->
        <div class="edit-header-bar">
            <div class="edit-header-back">
                <a href="{{ route('seller.products.index') }}">
                    <i class="fa fa-arrow-left"></i>
                </a>
            </div>
            <div class="edit-header-title">
                Edit Produk
            </div>
            <div class="edit-header-spacer"></div>
        </div>

        <div class="form-section">
            @if ($errors->any())
                <div class="error-alert">
                    <strong>⚠️ Terjadi kesalahan:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="success-alert">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
            @endif

            <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
                id="editForm">
                @csrf
                @method('PUT')

                <!-- Basic Info -->
                <div class="form-card">
                    <div class="form-card-title">Informasi Dasar</div>

                    <div class="form-group">
                        <label class="form-label">Kode Barang</label>
                        <div class="code-display">{{ $product->code }}</div>
                        <small class="form-hint">⚡ Kode akan berubah otomatis jika kategori diubah</small>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">
                            Kategori <span class="required">*</span>
                        </label>
                        <select name="category_id" id="category_id"
                            class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nama Barang <span class="required">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}"
                            required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="condition" class="form-label">Kondisi</label>
                        <input type="text" name="condition" id="condition"
                            class="form-input @error('condition') is-invalid @enderror"
                            value="{{ old('condition', $product->condition) }}" placeholder="Contoh: Baik, Bekas, Baru">
                        @error('condition')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="form-card">
                    <div class="form-card-title">Deskripsi</div>

                    <div class="form-group">
                        <label for="description" class="form-label">Deskripsi Barang</label>
                        <textarea name="description" id="description" class="form-textarea @error('description') is-invalid @enderror"
                            placeholder="Jelaskan detail barang...">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Images -->
                <div class="form-card">
                    <div class="form-card-title">📸 Foto Barang</div>

                    @if ($product->images->count() > 0)
                        <div class="images-section-title">
                            <span>Foto Tersimpan</span>
                            <span class="badge-count" id="existing-count">{{ $product->images->count() }}</span>
                        </div>
                        <div class="existing-images" id="existing-images-container">
                            @foreach ($product->images as $image)
                                <div class="existing-image-item" id="image-{{ $image->id }}">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="Product Image">
                                    <button type="button" class="delete-image-btn"
                                        onclick="deleteExistingImage({{ $image->id }})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="info-box">
                            <p><strong>ℹ️ Catatan:</strong> Foto lama akan tetap ada. Anda bisa hapus foto yang tidak
                                diinginkan atau tambah foto baru.</p>
                        </div>

                        <div class="divider"></div>
                    @endif

                    <div class="image-upload-wrapper">
                        <div class="images-section-title">
                            <span>Tambah Foto Baru</span>
                            <span class="badge-count" id="new-count" style="display: none;">0</span>
                        </div>

                        <input type="file" name="images[]" id="images" multiple
                            accept="image/jpeg,image/png,image/jpg" style="display: none;"
                            class="@error('images.*') is-invalid @enderror">

                        <label for="images" class="image-upload-section">
                            <i class="fa fa-camera upload-icon"></i>
                            <span class="upload-text">Klik untuk tambah foto baru</span>
                            <span class="upload-hint">Format: JPG, PNG • Max: 2MB per foto<br>Bisa pilih banyak foto
                                sekaligus</span>
                        </label>

                        @error('images.*')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror

                        <div id="new-image-preview" class="new-image-preview"></div>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-card">
                    <div class="form-card-title">Status Barang</div>

                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" name="is_maintenance" id="is_maintenance" value="1"
                                {{ old('is_maintenance', $product->is_maintenance) ? 'checked' : '' }}>
                            <label for="is_maintenance" class="form-label">
                                🔧 Barang sedang maintenance
                            </label>
                        </div>
                        <small class="form-hint">Centang jika barang sedang dalam perbaikan/maintenance dan tidak bisa
                            disewakan</small>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="submit-btn">
                    <i class="fa fa-save"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        let newFiles = [];

        // Preview new images yang akan diupload
        document.getElementById('images').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const preview = document.getElementById('new-image-preview');
            const newCountBadge = document.getElementById('new-count');

            // Tambahkan file baru ke array
            newFiles = [...newFiles, ...files];

            // Update preview
            updateNewImagePreview();

            // Update badge count
            if (newFiles.length > 0) {
                newCountBadge.textContent = newFiles.length;
                newCountBadge.style.display = 'inline-block';
            }

            // Reset input file supaya bisa upload lagi
            e.target.value = '';
        });

        function updateNewImagePreview() {
            const preview = document.getElementById('new-image-preview');
            preview.innerHTML = '';

            newFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'new-image-item';
                    div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <span class="new-badge">BARU</span>
                <button type="button" class="remove-new-image" onclick="removeNewImage(${index})">
                    <i class="fa fa-times"></i>
                </button>
            `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }

        function removeNewImage(index) {
            // Hapus file dari array
            newFiles.splice(index, 1);

            // Update preview
            updateNewImagePreview();

            // Update badge count
            const newCountBadge = document.getElementById('new-count');
            if (newFiles.length > 0) {
                newCountBadge.textContent = newFiles.length;
            } else {
                newCountBadge.style.display = 'none';
            }

            // Update input file
            updateFileInput();
        }

        function updateFileInput() {
            const input = document.getElementById('images');
            const dataTransfer = new DataTransfer();

            newFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            input.files = dataTransfer.files;
        }

        // Delete existing image (dari database dan storage)
        function deleteExistingImage(imageId) {
            if (!confirm('❌ Yakin ingin menghapus foto ini?\n\nFoto akan dihapus permanen dari database dan storage.')) {
                return;
            }

            const imageElement = document.getElementById(`image-${imageId}`);
            const deleteBtn = imageElement.querySelector('.delete-image-btn');

            // Disable button
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            fetch(`/seller/products/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Hapus element dari DOM dengan animasi
                        imageElement.style.transition = 'all 0.3s ease';
                        imageElement.style.opacity = '0';
                        imageElement.style.transform = 'scale(0.8)';

                        setTimeout(() => {
                            imageElement.remove();

                            // Update count badge
                            const container = document.getElementById('existing-images-container');
                            const remainingImages = container.querySelectorAll('.existing-image-item').length;
                            document.getElementById('existing-count').textContent = remainingImages;

                            // Jika tidak ada foto lagi, sembunyikan section
                            if (remainingImages === 0) {
                                container.parentElement.remove();
                            }
                        }, 300);
                    } else {
                        alert('❌ Gagal menghapus foto: ' + (data.message || 'Unknown error'));
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = '<i class="fa fa-times"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat menghapus foto');
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="fa fa-times"></i>';
                });
        }

        // Submit form dengan file baru
        document.getElementById('editForm').addEventListener('submit', function(e) {
            // Update input file dengan file yang sudah dipilih
            if (newFiles.length > 0) {
                updateFileInput();
            }
        });
    </script>
@endsection
