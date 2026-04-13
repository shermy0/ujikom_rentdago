@extends('admin.layouts.app')

@section('content')
<div class="profile-edit-container">
    <div class="row">
        <!-- Left Column - Profile Info -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-sidebar-card">
                <div class="card-body text-center">
                    <!-- Current Avatar -->
                    <div class="current-avatar-wrapper mb-3">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="current-avatar"
                                 id="avatarPreview">
                        @else
                            <div class="current-avatar-placeholder" id="avatarPlaceholder">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <img src="" alt="" class="current-avatar d-none" id="avatarPreview">
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">
                        <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'seller' ? 'primary' : 'success') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </p>
                    <p class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        Bergabung {{ $user->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column - Edit Forms -->
        <div class="col-lg-8">
            <!-- Profile Info Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear me-2"></i>Edit Profil
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Upload -->
                        <div class="mb-4">
                            <label class="form-label">Foto Profil</label>
                            <div class="avatar-upload-wrapper">
                                <input type="file" 
                                       name="avatar" 
                                       id="avatarInput" 
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                <small class="text-muted d-block mt-1">
                                    Format: JPG, PNG, GIF. Maksimal 2MB
                                </small>
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Change Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Ubah Password
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control @error('password') is-invalid @enderror"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .profile-edit-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px 25px;
        border-radius: 12px 12px 0 0 !important;
    }

    .card-header h5 {
        color: #333;
        font-weight: 600;
    }

    .card-body {
        padding: 25px;
    }

    .profile-sidebar-card {
        position: sticky;
        top: 80px;
    }

    .current-avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .current-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .current-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 600;
        color: white;
    }

    .form-label {
        font-weight: 500;
        color: #444;
    }

    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
    }

    .form-control:focus {
        border-color: #ee4d2d;
        box-shadow: 0 0 0 3px rgba(238, 77, 45, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border: none;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 8px;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #d94429, #e55a2b);
    }

    .btn-warning {
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 8px;
    }

    .input-group .btn {
        border-color: #e0e0e0;
        z-index: 0;
    }

    .input-group .form-control.is-invalid {
        z-index: 2;
    }

    @media (max-width: 991px) {
        .profile-sidebar-card {
            position: relative;
            top: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Avatar preview
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const placeholder = document.getElementById('avatarPlaceholder');
                
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
</script>
@endpush
@endsection
