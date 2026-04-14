@extends('frontend.masterseller')

@section('content')
<style>
* {
    box-sizing: border-box;
}

.voucher-container {
    padding: 24px 16px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.page-title-section {
    flex: 1;
    min-width: 200px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.filter-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-input {
    flex: 1;
    min-width: 250px;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
}

.filter-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-select {
    min-width: 180px;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-secondary {
    background: #6b7280;
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-secondary:hover {
    background: #4b5563;
}

.voucher-grid {
    display: grid;
    gap: 20px;
    margin-bottom: 24px;
}

.voucher-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    border: 2px solid transparent;
}

.voucher-card:hover {
    border-color: #A20B0B;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
    transform: translateY(-4px);
}

.voucher-header {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f3f4f6;
}

.voucher-badge {
    background: linear-gradient(135deg, #A20B0B 0%, #770C0C 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    min-width: 120px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.voucher-discount {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 4px;
}

.voucher-type {
    font-size: 11px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.voucher-info-main {
    flex: 1;
}

.voucher-name {
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.voucher-code {
    display: inline-block;
    background: #f3f4f6;
    padding: 6px 14px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    margin-bottom: 8px;
}

.voucher-description {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.voucher-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 10px;
}

.detail-icon {
    width: 36px;
    height: 36px;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 16px;
}

.detail-content {
    flex: 1;
}

.detail-label {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.detail-value {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.voucher-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.voucher-status {
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

.status-active {
    background: #dcfce7;
    color: #16a34a;
}

.status-inactive {
    background: #fee2e2;
    color: #dc2626;
}

.status-expired {
    background: #f3f4f6;
    color: #6b7280;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center; /* Tambahkan ini */
}

/* Cari bagian ini di CSS kamu dan ganti menjadi: */
.action-buttons form {
    display: inline-block; /* Ubah dari flex: 1 ke inline-block */
    margin: 0;
}

.action-buttons form button.btn-icon {
    width: 40px; /* Pastikan width tetap 40px */
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 56px;
}

.empty-state h3 {
    font-size: 24px;
    color: #1f2937;
    margin-bottom: 12px;
}

.empty-state p {
    color: #6b7280;
    font-size: 16px;
    margin-bottom: 24px;
}

.pagination-wrapper {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .voucher-container {
        padding: 16px 12px;
    }
    
    .page-header {
        padding: 20px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .page-title {
        font-size: 22px;
    }
    
    .btn-primary {
        width: 100%;
        justify-content: center;
    }
    
    .filter-row {
        flex-direction: column;
    }
    
    .filter-input,
    .filter-select {
        width: 100%;
        min-width: auto;
    }
    
    .voucher-header {
        flex-direction: column;
    }
    
    .voucher-badge {
        width: 100%;
        padding: 16px;
    }
    
    .voucher-details {
        grid-template-columns: 1fr;
    }
    
    .voucher-footer {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-buttons {
        justify-content: center;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .voucher-details {
        grid-template-columns: repeat(2, 1fr);
    }
}
.btn-primary {
    background-color: #ffffff;
    color: #A20B0B;
    border: 2px solid #A20B0B;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s ease;
}

.btn-primary i {
    color: #A20B0B;
}

.btn-primary:hover {
    background-color: #A20B0B;
    color: #ffffff;
}

.btn-primary:hover i {
    color: #ffffff;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
}


</style>

    <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.dashboard.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Kelola Voucher
        </div>
        <div class="create-header-spacer"></div>
    </div>

<div class="voucher-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="page-title-section">
                <h1 class="page-title">
                    <i class="fa fa-ticket"></i> Kelola Voucher
                </h1>
                <p class="page-subtitle">Buat dan kelola voucher untuk meningkatkan penjualan</p>
            </div>
            <a href="{{ route('seller.vouchers.create') }}" class="btn-primary">
                <i class="fa fa-plus"></i> Buat Voucher Baru
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari kode atau nama voucher..." 
                    value="{{ request('search') }}"
                    class="filter-input"
                >
                
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="btn-primary">
                    <i class="fa fa-search"></i> Cari
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('seller.vouchers.index') }}" class="btn-secondary">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Voucher List -->
    @if($vouchers->count() > 0)
        <div class="voucher-grid">
            @foreach($vouchers as $voucher)
                <div class="voucher-card">
                    <!-- Header -->
                    <div class="voucher-header">
                        <div class="voucher-badge">
                            <div class="voucher-discount">
                                {{ $voucher->formatted_discount }}
                            </div>
                            <div class="voucher-type">
                                {{ $voucher->discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}
                            </div>
                        </div>

                        <div class="voucher-info-main">
                            <div class="voucher-name">{{ $voucher->name }}</div>
                            <div class="voucher-code">{{ $voucher->code }}</div>
                            @if($voucher->description)
                                <div class="voucher-description">{{ $voucher->description }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="voucher-details">
                        @if($voucher->min_transaction > 0)
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Min. Belanja</div>
                                    <div class="detail-value">Rp {{ number_format($voucher->min_transaction, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($voucher->valid_until)
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fa fa-clock"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Berlaku Hingga</div>
                                    <div class="detail-value">{{ $voucher->valid_until->format('d M Y') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($voucher->remaining_usage)
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fa fa-ticket"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Sisa Kuota</div>
                                    <div class="detail-value">{{ $voucher->remaining_usage }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="voucher-footer">
                        <span class="voucher-status status-{{ strtolower($voucher->status_label) === 'aktif' ? 'active' : (strtolower($voucher->status_label) === 'nonaktif' ? 'inactive' : 'expired') }}">
                            {{ $voucher->status_label }}
                        </span>

                        <div class="action-buttons">
                            <a href="{{ route('seller.vouchers.show', $voucher->id) }}" class="btn-icon btn-view" title="Lihat Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            
                            <a href="{{ route('seller.vouchers.edit', $voucher->id) }}" class="btn-icon btn-edit" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>

                            <button 
                                onclick="toggleStatus({{ $voucher->id }}, {{ $voucher->is_active ? 'false' : 'true' }})" 
                                class="btn-icon btn-toggle {{ $voucher->is_active ? '' : 'inactive' }}"
                                title="{{ $voucher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                            >
                                <i class="fa fa-{{ $voucher->is_active ? 'ban' : 'check' }}"></i>
                            </button>

                            <form id="delete-form-{{ $voucher->id }}" action="{{ route('seller.vouchers.destroy', $voucher->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button" class="btn-icon btn-delete btn-delete-voucher" data-id="{{ $voucher->id }}" title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $vouchers->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>Belum Ada Voucher</h3>
            <p>Buat voucher pertama Anda untuk menarik lebih banyak pelanggan!</p>
            <a href="{{ route('seller.vouchers.create') }}" class="btn-primary">
                <i class="fa fa-plus"></i> Buat Voucher Sekarang
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete Confirmation
    document.querySelectorAll('.btn-delete-voucher').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('delete-form-' + id);

            Swal.fire({
                title: 'Hapus Voucher?',
                text: 'Voucher akan dihapus permanen. Lanjutkan?',
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

function toggleStatus(voucherId, activate) {
    Swal.fire({
        title: activate ? 'Aktifkan Voucher?' : 'Nonaktifkan Voucher?',
        text: activate ? 'Voucher akan dapat digunakan oleh pelanggan' : 'Voucher tidak akan dapat digunakan sementara',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/seller/vouchers/${voucherId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan',
                    confirmButtonColor: '#3b82f6'
                });
            });
        }
    });
}
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonColor: '#3b82f6'
    });
</script>
@endif

@endsection