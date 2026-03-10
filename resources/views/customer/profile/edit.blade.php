@extends('frontend.master')
@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left" style="flex: none; background: transparent; padding: 0;">
        <a href="{{ route('profile.index') }}" style="color: #fff; font-size: 20px;">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div style="flex: 1; text-align: center;">
        <span style="color: #fff; font-size: 18px; font-weight: 500;">Edit Profil</span>
    </div>
    <div class="header-right"></div>
</div>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Alert Messages -->
    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Avatar Section -->
        <div class="bg-white py-4">
            <div class="text-center">
                <div class="position-relative d-inline-block mb-3">
                    <div id="avatar-container">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="Avatar" 
                                 id="avatar-preview"
                                 class="rounded-circle"
                                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #ff5722;">
                        @else
                            <div id="avatar-preview" class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                 style="width: 100px; height: 100px; border: 3px solid #ff5722;">
                                <i class="fa fa-user text-white" style="font-size: 40px;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <label for="avatar-input" 
                           class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                           style="width: 32px; height: 32px; cursor: pointer; border: 2px solid #fff;">
                        <i class="fa fa-camera" style="font-size: 14px;"></i>
                    </label>
                    <input type="file" 
                           id="avatar-input" 
                           name="avatar" 
                           accept="image/*" 
                           class="d-none">
                </div>
                <small class="text-muted d-block">Klik ikon kamera untuk mengganti foto</small>
            </div>
        </div>

        <!-- Form Section -->
        <div class="p-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="phone" class="form-label fw-bold">No. Telepon</label>
                        <input type="text" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="Contoh: 08123456789"
                               required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pastikan nomor telepon aktif</small>
                    </div>
                </div>
            </div>
        </div>

             <!-- Security Section -->
        <div class="p-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 fw-bold">Keamanan Akun</h6>
                            <small class="text-muted">Kelola password akun Anda</small>
                        </div>
                        <a href="{{ route('profile.reset.password') }}" 
                           class="btn btn-outline-danger btn-sm">
                            <i class="fa fa-key me-1"></i>Reset Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="p-3">
            <button type="submit" class="btn btn-primary w-100 mb-5">
                <i class="fa fa-save me-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            this.value = '';
            return;
        }

        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPEG, JPG, atau PNG');
            this.value = '';
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(event) {
            const container = document.getElementById('avatar-container');
            container.innerHTML = `<img src="${event.target.result}" 
                                       id="avatar-preview"
                                       class="rounded-circle" 
                                       style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #ff5722;">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection