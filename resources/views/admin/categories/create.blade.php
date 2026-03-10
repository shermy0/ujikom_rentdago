@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-plus-circle me-2"></i>Tambah Kategori Baru
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
          
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama kategori"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Slug akan dibuat otomatis dari nama kategori.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Kategori</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror"
                                id="parent_id"
                                name="parent_id">
                            <option value="">-- Tidak Ada (Kategori Utama) --</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pilih parent jika ini adalah sub-kategori.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon Kategori <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control @error('icon') is-invalid @enderror"
                               id="icon"
                               name="icon"
                               accept="image/*"
                               required>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Upload gambar untuk icon kategori (JPG, PNG, GIF, SVG, WEBP - Max 2MB)
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Preview Icon</label>
                        <div class="icon-preview p-3 border rounded text-center" id="iconPreview">
                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0 small">Upload gambar untuk melihat preview</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    .card-header {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-body {
        padding: 25px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #d94429, #e55a2b);
    }
    .form-label {
        font-weight: 500;
        color: #333;
    }
    .icon-preview {
        background: linear-gradient(135deg, #f8f9fa, #fff);
        min-height: 80px;
    }
    .icon-preview .category-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Preview Icon
    document.getElementById('icon').addEventListener('change', function() {
        const preview = document.getElementById('iconPreview');
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="max-width: 80px; max-height: 80px; border-radius: 8px; object-fit: cover;">
                    <p class="text-muted mt-2 mb-0 small">${file.name}</p>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `
                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0 small">Upload gambar untuk melihat preview</p>
            `;
        }
    });

    // Form Validation with SweetAlert
    document.addEventListener('DOMContentLoaded', function() {
        // Check for session messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ee4d2d'
            });
        @endif
    });
</script>
@endpush
@endsection
