@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-shop me-2"></i>Daftar Toko
            </h5>
            @php
                $availableSellers = \App\Models\User::where('role', 'seller')
                    ->doesntHave('shop')
                    ->count();
            @endphp
        </div>
        <div class="card-body">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Filter & Search -->
            <form action="{{ route('admin.shops.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nama atau alamat toko..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
                @if (request('search') || request('status'))
                    <div class="col-md-2">
                        <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Reset
                        </a>
                    </div>
                @endif
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 70px;">Logo</th>
                            <th>Nama Toko</th>
                            <th>Pemilik</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shops as $shop)
                            <tr id="shop-row-{{ $shop->id }}">
                                <td>{{ $shop->id }}</td>
                                <td>
                                    @if ($shop->logo)
                                        <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name_store }}"
                                            class="shop-logo rounded">
                                    @else
                                        <div class="shop-logo-placeholder rounded">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $shop->name_store }}</strong>
                                    <br><small class="text-muted">{{ $shop->slug }}</small>
                                </td>
                                <td>
                                    @if ($shop->user)
                                        <span class="badge bg-info">{{ $shop->user->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ Str::limit($shop->address_store, 40) }}</small>
                                </td>
                                <td>
                                    <span class="badge status-badge {{ $shop->is_active ? 'bg-success' : 'bg-secondary' }}"
                                          id="status-badge-{{ $shop->id }}">
                                        <i class="bi {{ $shop->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                        {{ $shop->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>{{ $shop->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.shops.show', $shop) }}"
                                           class="btn btn-outline-info"
                                           title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <form action="{{ route('admin.shops.toggle-status', $shop) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirmToggle(event, '{{ $shop->name_store }}', {{ $shop->is_active ? 'true' : 'false' }})">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm {{ $shop->is_active ? 'btn-danger' : 'btn-success' }} text-white"
                                                    title="{{ $shop->is_active ? 'Nonaktifkan Toko' : 'Aktifkan Toko' }}">
                                                {{ $shop->is_active ? 'Nonactive' : 'Active' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-shop display-4 d-block mb-2"></i>
                                    Belum ada data toko
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($shops->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $shops->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .card {
                border: none;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
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

            .table th {
                font-weight: 600;
                color: #666;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge {
                font-weight: 500;
                padding: 6px 10px;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                transition: all 0.3s ease;
            }

            .status-badge i {
                font-size: 12px;
            }

            .shop-logo {
                width: 45px;
                height: 45px;
                object-fit: cover;
            }

            .shop-logo-placeholder {
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #ee4d2d, #ff6b35);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 18px;
            }

            .btn-toggle-status {
                transition: all 0.3s ease;
            }

            .btn-toggle-status:hover {
                transform: scale(1.1);
            }

            /* Button Locked Style */
            .btn-locked {
                opacity: 0.75;
                cursor: pointer;
                position: relative;
            }

            .btn-locked:hover {
                opacity: 0.9;
            }

            /* SweetAlert2 Custom Styling */
            .swal2-popup {
                border-radius: 12px;
                padding: 2rem;
            }

            .swal2-title {
                font-size: 1.5rem;
                font-weight: 600;
            }

            .swal2-html-container {
                font-size: 1rem;
            }

            .swal2-confirm {
                padding: 10px 24px !important;
                border-radius: 8px !important;
                font-weight: 500 !important;
            }

            .swal2-cancel {
                padding: 10px 24px !important;
                border-radius: 8px !important;
                font-weight: 500 !important;
            }
        </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Alert untuk tombol tambah toko yang terkunci
    function showNoSellerAlert() {
        Swal.fire({
            title: 'Admin tidak dapat membuat toko',
            text: 'Pembuatan toko hanya dapat dilakukan oleh seller melalui panel seller. Apakah Anda yakin ingin menambahkan toko baru?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('admin.shops.create') }}';
            }
        });
    }

    // Konfirmasi toggle status dengan SweetAlert
    function confirmToggle(event, shopName, isActive) {
        event.preventDefault();
        const form = event.target;
        const action = isActive ? 'nonaktifkan' : 'aktifkan';

        Swal.fire({
            title: isActive ? 'Nonaktifkan Toko?' : 'Aktifkan Toko?',
            html: `
                <p style="margin: 0; color: #666; font-size: 15px;">
                    Apakah Anda yakin ingin ${action} toko
                    <strong style="color: #333;">${shopName}</strong>?
                </p>
                ${isActive ? '<p style="margin-top: 10px; color: #e74c3c; font-size: 14px;"><i class="bi bi-exclamation-triangle"></i> Toko yang dinonaktifkan tidak akan muncul di customer dan tidak bisa menerima pesanan.</p>' : ''}
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Ya, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
            cancelButtonText: 'Batal',
            confirmButtonColor: isActive ? '#dc3545' : '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }
    </script>
    @endpush
@endsection
