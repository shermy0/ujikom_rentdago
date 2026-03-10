@extends('frontend.masterseller')

@section('content')
<style>
    .courier-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding-bottom: 80px;
    }
    
    .courier-header-bar {
        background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .courier-header-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .courier-header-back a {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
    }
    
    .courier-header-title {
        flex: 1;
        text-align: center;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
    }
    
    .courier-header-spacer {
        width: 40px;
    }
    
    .courier-stats {
        background: #fff;
        margin: 1rem;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #ff5722;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .courier-actions {
        padding: 0 1rem;
        margin-bottom: 1rem;
    }
    
    .btn-add-courier {
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-add-courier:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        transform: translateY(-2px);
    }
    
    .courier-list {
        padding: 0 1rem;
    }
    
    .courier-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .courier-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .courier-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        flex-shrink: 0;
    }
    
    .courier-info {
        flex: 1;
    }
    
    .courier-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .courier-phone {
        font-size: 0.875rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .courier-status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .courier-status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    
    .courier-status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .courier-meta {
        display: flex;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid #e0e0e0;
        margin-bottom: 1rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .courier-actions-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }
    
    .btn-courier-action {
        height: 40px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-edit {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .btn-edit:hover {
        background: #bbdefb;
    }
    
    .btn-toggle {
        background: #fff3cd;
        color: #856404;
    }
    
    .btn-toggle:hover {
        background: #ffc107;
    }
    
    .btn-delete {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-delete:hover {
        background: #f5c6cb;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .empty-text {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>

<div class="courier-container">
    <!-- Header -->
        <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.mypage.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Kurir Saya
        </div>
        <div class="create-header-spacer"></div>
    </div>
    <!-- Alert Messages -->
    {{-- @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
        <i class="fa fa-check-circle"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="margin: 1rem; border-radius: 10px;">
        <i class="fa fa-exclamation-circle"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif --}}

    <!-- Stats -->
    <div class="courier-stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $couriers->count() }}</div>
                <div class="stat-label">Total Kurir</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $couriers->where('status', 'active')->count() }}</div>
                <div class="stat-label">Kurir Aktif</div>
            </div>
        </div>
    </div>

    <!-- Add Button -->
    <div class="courier-actions">
        <a href="{{ route('seller.couriers.create') }}" class="btn-add-courier">
            <i class="fa fa-plus-circle"></i>
            <span>Tambah Kurir Baru</span>
        </a>
    </div>

    <!-- Courier List -->
    <div class="courier-list">
        @forelse($couriers as $courier)
        <div class="courier-card">
            <div class="courier-header">
                <div class="courier-avatar">
                    {{ strtoupper(substr($courier->user->name, 0, 1)) }}
                </div>
                <div class="courier-info">
                    <div class="courier-name">{{ $courier->user->name }}</div>
                    <div class="courier-phone">
                        <i class="fa fa-phone"></i>
                        {{ $courier->user->phone }}
                    </div>
                </div>
                <span class="courier-status-badge {{ $courier->status }}">
                    <i class="fa fa-circle"></i>
                    {{ ucfirst($courier->status) }}
                </span>
            </div>

            <div class="courier-meta">
                <div class="meta-item">
                    <i class="fa fa-calendar-plus"></i>
                    {{ $courier->created_at->format('d M Y') }}
                </div>
                <div class="meta-item">
                    <i class="fa fa-user-tie"></i>
                    {{ $courier->creator->name }}
                </div>
            </div>

            <div class="courier-actions-row">
                <form action="{{ route('seller.couriers.toggle', $courier->id) }}" 
                      method="POST" 
                      style="margin: 0;">
                    @csrf
                    <button type="submit" 
                            class="btn-courier-action btn-toggle"
                            onclick="return confirm('Yakin ingin mengubah status kurir ini?')">
                        <i class="fa fa-toggle-on"></i>
                        Toggle
                    </button>
                </form>

                <form action="{{ route('seller.couriers.destroy', $courier->id) }}" 
                      method="POST" 
                      style="margin: 0;">
                    @csrf
                    @method('DELETE')
<button type="button"
        class="btn-courier-action btn-delete btn-delete-courier"
        data-id="{{ $courier->id }}">
    <i class="fa fa-trash"></i>
    Hapus
</button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa fa-shipping-fast"></i>
            </div>
            <div class="empty-title">Belum Ada Kurir</div>
            <div class="empty-text">
                Tambahkan kurir untuk membantu pengiriman produk Anda
            </div>
        </div>
        @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===============================
    // DELETE COURIER CONFIRMATION
    // ===============================
    document.querySelectorAll('.btn-delete-courier').forEach(button => {
        button.addEventListener('click', function () {

            const form = this.closest('form');

            Swal.fire({
                title: 'Hapus Kurir?',
                text: 'Kurir akan dihapus permanen. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

        });
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}"
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}"
        });
    @endif

});
</script>

@endsection