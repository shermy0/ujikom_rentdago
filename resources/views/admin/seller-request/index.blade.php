@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success text-white mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger text-white mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">Daftar Pengajuan Seller</h5>
                <p class="text-muted small mb-0">Kelola verifikasi calon seller.</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                <span class="fw-bold">{{ $requests->total() }}</span> Total Data
            </span>
        </div>

        <div class="card-body p-4">
            
            <!-- Modern Filter Tabs -->
            <div class="mb-4 bg-light p-1 rounded-pill d-inline-flex seller-filter-tabs">
                <a href="{{ route('admin.seller-requests.index', ['status' => 'all']) }}"
                    class="btn btn-sm rounded-pill px-4 seller-filter-btn {{ request('status') == 'all' ? 'btn-primary text-white shadow-sm' : 'text-muted' }}">
                    Semua
                </a>
                <a href="{{ route('admin.seller-requests.index', ['status' => 'pending']) }}"
                    class="btn btn-sm rounded-pill px-4 seller-filter-btn {{ !request('status') || request('status') == 'pending' ? 'btn-warning text-white shadow-sm' : 'text-muted' }}">
                    Pending
                </a>
                <a href="{{ route('admin.seller-requests.index', ['status' => 'approved']) }}"
                    class="btn btn-sm rounded-pill px-4 seller-filter-btn {{ request('status') == 'approved' ? 'btn-success text-white shadow-sm' : 'text-muted' }}">
                    Approved
                </a>
                <a href="{{ route('admin.seller-requests.index', ['status' => 'rejected']) }}"
                    class="btn btn-sm rounded-pill px-4 seller-filter-btn {{ request('status') == 'rejected' ? 'btn-danger text-white shadow-sm' : 'text-muted' }}">
                    Rejected
                </a>
            </div>

            <!-- Table -->
            <div class="table-responsive bg-white rounded-3 border">
                <table class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-bold text-uppercase small border-0" style="width: 50px;">#</th>
                            <th class="text-muted fw-bold text-uppercase small border-0">Nama User</th>
                            <th class="text-muted fw-bold text-uppercase small border-0">Kontak</th>
                            <th class="text-muted fw-bold text-uppercase small border-0">Status</th>
                            <th class="text-muted fw-bold text-uppercase small border-0">Tanggal Pengajuan</th>
                            <th class="text-center text-muted fw-bold text-uppercase small border-0 pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr class="border-bottom">
                                <td class="ps-4 text-muted">{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px symbol-circle me-3 bg-light-primary d-flex align-items-center justify-content-center text-primary fw-bold">
                                            {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <span class="text-dark fw-bold mb-1">{{ $request->user->name }}</span>
                                            <span class="text-muted small">ID: #{{ $request->user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-semibold mb-1">
                                            <i class="bi bi-phone me-1 text-muted"></i>{{ $request->user->phone }}
                                        </span>
                                        @if($request->user->phone_verified_at)
                                            <span class="badge badge-light-success small">
                                                <i class="bi bi-check-circle"></i> Terverifikasi
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($request->status == 'pending')
                                        <span class="badge badge-light-warning text-warning fw-bold px-3 py-2 rounded-3 d-inline-flex align-items-center">
                                            <span class="bullet bullet-dot bg-warning h-6px w-6px rounded-circle me-2"></span> Pending
                                        </span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge badge-light-success text-success fw-bold px-3 py-2 rounded-3 d-inline-flex align-items-center">
                                            <span class="bullet bullet-dot bg-success h-6px w-6px rounded-circle me-2"></span> Approved
                                        </span>
                                    @else
                                        <span class="badge badge-light-danger text-danger fw-bold px-3 py-2 rounded-3 d-inline-flex align-items-center">
                                            <span class="bullet bullet-dot bg-danger h-6px w-6px rounded-circle me-2"></span> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block">{{ $request->created_at->format('d M Y') }}</span>
                                    <span class="text-muted small">{{ $request->created_at->format('H:i') }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-icon btn-sm btn-light-info btn-color-muted btn-active-info active" 
                                            data-bs-toggle="modal" data-bs-target="#detailModal{{ $request->id }}" title="Lihat Detail">
                                            <i class="bi bi-eye fs-5"></i>
                                        </button>

                                        @if ($request->status == 'pending')
                                            <!-- Active Button (Approve) -->
                                            <button type="button" class="btn btn-sm btn-success text-white" 
                                                data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}" title="Aktifkan Seller">
                                                Aktifkan
                                            </button>

                                            <!-- Nonactive Button (Reject) -->
                                            <button type="button" class="btn btn-sm btn-danger text-white" 
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}" title="Nonaktifkan Seller">
                                                Nonaktifkan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Pengajuan Seller
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-4">
                                                <!-- Kolom Kiri: Data User -->
                                                <div class="col-lg-6">
                                                    <div class="bg-light rounded-3 p-4">
                                                        <h6 class="fw-bold mb-3 text-primary">
                                                            <i class="bi bi-person-circle me-2"></i>Data Pemohon
                                                        </h6>
                                                        
                                                        <div class="mb-3">
                                                            <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                                            <span class="fw-bold text-dark">{{ $request->user->name }}</span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted d-block mb-1">Nomor HP</small>
                                                            <span class="fw-bold text-dark">{{ $request->user->phone }}</span>
                                                            @if($request->user->phone_verified_at)
                                                                <span class="badge badge-light-success small ms-2">
                                                                    <i class="bi bi-check-circle"></i> Verified
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted d-block mb-1">Role Saat Ini</small>
                                                            <span class="badge bg-secondary">{{ ucfirst($request->user->role) }}</span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted d-block mb-1">Tanggal Pengajuan</small>
                                                            <span class="fw-bold text-dark">{{ $request->created_at->format('d F Y, H:i') }}</span>
                                                        </div>

                                                        <div class="mb-0">
                                                            <small class="text-muted d-block mb-1">Status</small>
                                                            @if ($request->status == 'pending')
                                                                <span class="badge badge-light-warning">
                                                                    <i class="bi bi-clock-history"></i> Menunggu Review
                                                                </span>
                                                            @elseif($request->status == 'approved')
                                                                <span class="badge badge-light-success">
                                                                    <i class="bi bi-check-circle"></i> Disetujui
                                                                </span>
                                                            @else
                                                                <span class="badge badge-light-danger">
                                                                    <i class="bi bi-x-circle"></i> Ditolak
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if ($request->status != 'pending')
                                                        <div class="alert alert-{{ $request->status == 'approved' ? 'success' : 'danger' }} mt-3 border-0 d-flex align-items-start p-3">
                                                            <i class="bi bi-{{ $request->status == 'approved' ? 'check' : 'x' }}-circle-fill fs-4 me-3"></i>
                                                            <div>
                                                                <strong>Catatan Admin:</strong>
                                                                <div class="small mt-1">{{ $request->admin_notes ?? '-' }}</div>
                                                                @if($request->reviewed_at)
                                                                    <small class="text-muted d-block mt-2" style="font-size: 11px;">
                                                                        <i class="bi bi-clock"></i> {{ $request->reviewed_at->format('d M Y, H:i') }}
                                                                        @if($request->reviewer)
                                                                            oleh {{ $request->reviewer->name }}
                                                                        @endif
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Kolom Kanan: Foto KTP -->
                                                <div class="col-lg-6">
                                                    <div class="bg-light rounded-3 p-4">
                                                        <h6 class="fw-bold mb-3 text-primary">
                                                            <i class="bi bi-card-image me-2"></i>Dokumen Identitas
                                                        </h6>
                                                        <div class="text-center">
                                                            <label class="small text-muted d-block mb-3">Foto KTP</label>
                                                            <a href="{{ asset('storage/' . $request->ktp_photo) }}" target="_blank">
                                                                <img src="{{ asset('storage/' . $request->ktp_photo) }}"
                                                                    class="img-fluid rounded-3 border shadow-sm w-100" 
                                                                    style="max-height: 300px; object-fit: cover; cursor: pointer;" 
                                                                    alt="KTP"
                                                                    title="Klik untuk memperbesar">
                                                            </a>
                                                            <a href="{{ asset('storage/' . $request->ktp_photo) }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-light-primary w-100 mt-3">
                                                                <i class="bi bi-zoom-in me-1"></i> Lihat Ukuran Penuh
                                                            </a>
                                                        </div>

                                                        <div class="alert alert-info border-0 mt-3 p-2 small">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Pastikan foto KTP jelas dan dapat dibaca
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light text-muted fw-bold px-4" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0">
                                        <form action="{{ route('admin.seller-requests.approve', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold text-success">
                                                    <i class="bi bi-check-circle-fill me-2"></i>Approve Pengajuan
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin <strong>menyetujui</strong> pengajuan ini?</p>
                                                <div class="bg-light-success p-3 rounded-3 mb-3 d-flex align-items-center">
                                                    <i class="bi bi-person-check fs-2 me-3 text-success"></i>
                                                    <div>
                                                        <span class="d-block fw-bold text-dark">{{ $request->user->name }}</span>
                                                        <span class="small text-muted">{{ $request->user->phone }}</span>
                                                    </div>
                                                </div>
                                                <div class="alert alert-warning border-0 small mb-3">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    User akan dapat membuka toko setelah disetujui
                                                </div>
                                                <label class="form-label fw-bold">Catatan Admin (Opsional)</label>
                                                <textarea name="admin_notes" class="form-control bg-light border-0 rounded-3" rows="3" placeholder="Tambahkan catatan untuk seller..."></textarea>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success fw-bold px-4">Ya, Setujui</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0">
                                        <form action="{{ route('admin.seller-requests.reject', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold text-danger">
                                                    <i class="bi bi-x-circle-fill me-2"></i>Tolak Pengajuan
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin <strong>menolak</strong> pengajuan ini?</p>
                                                <div class="bg-light-danger p-3 rounded-3 mb-3 d-flex align-items-center">
                                                    <i class="bi bi-exclamation-triangle fs-2 me-3 text-danger"></i>
                                                    <div>
                                                        <span class="d-block fw-bold text-dark">{{ $request->user->name }}</span>
                                                        <span class="small text-muted">{{ $request->user->phone }}</span>
                                                    </div>
                                                </div>
                                                <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                <textarea name="admin_notes" class="form-control bg-light border-0 rounded-3" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light text-muted fw-bold" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger fw-bold px-4">Ya, Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox fs-1 text-muted mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="text-muted">Belum ada pengajuan seller</h5>
                                        <p class="small text-muted">Pengajuan baru akan muncul di sini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($requests->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ $requests->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Helper untuk warna background badge */
    .bg-light-primary { background-color: #eef6ff !important; color: #3699ff !important; }
    .bg-light-success { background-color: #e8fff3 !important; color: #1bc5bd !important; }
    .bg-light-danger { background-color: #fff2f0 !important; color: #f64e60 !important; }
    .bg-light-warning { background-color: #fff4de !important; color: #ffa800 !important; }

    /* Button Icon Styles */
    .btn-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
    }
    
    .btn-light-info { background-color: #eef6ff; color: #3699ff; border: none; }
    .btn-light-info:hover, .btn-light-info.active { background-color: #3699ff; color: #fff !important; box-shadow: 0 4px 10px rgba(54, 153, 255, 0.3); }

    .btn-light-success { background-color: #e8fff3; color: #1bc5bd; border: none; }
    .btn-light-success:hover, .btn-light-success.active { background-color: #1bc5bd; color: #fff !important; box-shadow: 0 4px 10px rgba(27, 197, 189, 0.3); }

    .btn-light-danger { background-color: #fff2f0; color: #f64e60; border: none; }
    .btn-light-danger:hover, .btn-light-danger.active { background-color: #f64e60; color: #fff !important; box-shadow: 0 4px 10px rgba(246, 78, 96, 0.3); }

    .btn-light-primary { background-color: #eef6ff; color: #3699ff; border: none; }
    .btn-light-primary:hover { background-color: #3699ff; color: #fff !important; }

    /* Table Styles */
    .table > :not(caption) > * > * {
        padding: 1.25rem 1rem;
        vertical-align: middle;
    }
    
    .table thead th {
        border-bottom-width: 1px;
        font-weight: 700;
        letter-spacing: 0.5px;
        background-color: #F3F6F9;
    }

    .table-row-dashed td {
        border-bottom-width: 1px;
        border-bottom-style: dashed;
        border-bottom-color: #eff2f5;
    }

    /* Avatar Symbol */
    .symbol-40px {
        width: 40px;
        height: 40px;
    }
    .symbol-circle {
        border-radius: 50%;
    }
    .text-hover-primary:hover {
        color: #3699ff !important;
    }

    /* Badge Light variants */
    .badge-light-success { color: #1bc5bd; background-color: #c9f7f5; padding: 6px 10px; border-radius: 6px; font-weight: 600; }
    .badge-light-warning { color: #ffa800; background-color: #fff4de; padding: 6px 10px; border-radius: 6px; font-weight: 600; }
    .badge-light-danger { color: #f64e60; background-color: #ffe2e5; padding: 6px 10px; border-radius: 6px; font-weight: 600; }

    /* Modal Style */
    .modal-content {
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
    }
    .modal-body {
        padding: 2rem;
    }
    
    /* Form Controls */
    .form-control:focus {
        box-shadow: none;
        border-color: #3699ff;
        background-color: #fff;
    }

    /* Image hover effect */
    img[style*="cursor: pointer"]:hover {
        transform: scale(1.02);
        transition: transform 0.2s;
    }

    .seller-filter-tabs {
        display: flex !important;
        flex-wrap: wrap;
        gap: 6px;
        max-width: 100%;
    }

    .seller-filter-btn {
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .seller-filter-tabs {
            width: 100%;
            border-radius: 12px !important;
        }

        .seller-filter-btn {
            flex: 1 1 calc(50% - 6px);
            text-align: center;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x;
        }

        .table-responsive .table {
            min-width: 760px;
        }
    }
</style>
@endpush
@endsection