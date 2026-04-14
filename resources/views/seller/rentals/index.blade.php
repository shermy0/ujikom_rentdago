@extends('frontend.masterseller')

@section('content')
    <style>
        .rentals-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
        }

        .rentals-header-bar {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .rentals-header-back {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rentals-header-back a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
        }

        .rentals-header-title {
            flex: 1;
            text-align: center;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .rentals-header-add {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rentals-header-add a {
            color: #fff;
            font-size: 20px;
            text-decoration: none;
        }

        .filter-section {
            background: #fff;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .filter-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            background: #fff;
        }

        .rentals-list {
            padding: 0 1rem 5rem 1rem;
        }

        .rental-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .rental-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .rental-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 2rem;
        }

        .rental-info {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-code {
            font-size: 0.75rem;
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .delivery-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .delivery-badge.pickup {
            background: #e3f2fd;
            color: #1976d2;
        }

        .delivery-badge.delivery {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .delivery-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .rental-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .detail-value.price {
            color: #28a745;
        }

        .detail-value.penalty {
            color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .alert {
            margin: 1rem;
            padding: 1rem;
            border-radius: 8px;
            position: relative;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-close {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-bottom: 1rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            color: #007bff;
            background: #fff;
            border: 1px solid #ddd;
        }

        .pagination .active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        /* Perbaiki layout rental-actions untuk 3 tombol */
        .rental-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .package-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff5722 100%);
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .rental-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
            /* Tambahkan ini */
        }

        .rental-package-info {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
    </style>

    <div class="rentals-container">
        <!-- Header -->
            <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.dashboard.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Paket Sewa
        </div>
                    <div class="rentals-header-add">
                <a href="{{ route('seller.rentals.create') }}">
                    <i class="fa fa-plus"></i>
                </a>
            </div>
    </div>

        <!-- Alert dihapus, diganti SweetAlert di bawah -->

        <!-- Filter Section -->
        <div class="filter-section">
            <form action="{{ route('seller.rentals.index') }}" method="GET">
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" name="search" placeholder="Cari nama atau kode barang..."
                        value="{{ request('search') }}">
                </div>

                <select name="delivery" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Metode Pengiriman</option>
                    <option value="pickup" {{ request('delivery') == 'pickup' ? 'selected' : '' }}>
                        Ambil Sendiri
                    </option>
                    <option value="delivery" {{ request('delivery') == 'delivery' ? 'selected' : '' }}>
                        Antar
                    </option>
                </select>
            </form>
        </div>

        <!-- Rentals List -->
        <div class="rentals-list">
            @forelse($rentals as $rental)
                <div class="rental-card">
                    <!-- Badge Paket (jika produk punya lebih dari 1 rental) -->
                    @php
                        $totalRentals = \App\Models\ProductRental::where('product_id', $rental->product_id)->count();
                    @endphp

                    @if ($totalRentals > 1)
                        <div class="package-badge">
                            <i class="fa fa-layer-group"></i> Paket {{ $rental->package_number ?? 1 }}
                        </div>
                    @endif

                    <div class="rental-header">
                        @if ($rental->product->images->count() > 0)
                            <img src="{{ asset('storage/' . $rental->product->images->first()->image_path) }}"
                                alt="{{ $rental->product->name }}" class="rental-image">
                        @else
                            <div class="rental-image">
                                <i class="fa fa-camera"></i>
                            </div>
                        @endif

                        <div class="rental-info">
                            <div class="product-name">
                                {{ $rental->product->name }}
                                @if ($totalRentals > 1)
                                    <span class="rental-package-info">
                                        <i class="fa fa-copy"></i>
                                        {{ $totalRentals }} Paket
                                    </span>
                                @endif
                            </div>
                            <div class="product-code">{{ $rental->product->code }}</div>
                            <div class="delivery-badges">
                                @if ($rental->is_delivery === 'pickup' || $rental->is_delivery === 'both')
                                    <span class="delivery-badge pickup">
                                        <i class="fa fa-walking"></i>
                                        <span>Ambil Sendiri</span>
                                    </span>
                                @endif
                                @if ($rental->is_delivery === 'delivery' || $rental->is_delivery === 'both')
                                    <span class="delivery-badge delivery">
                                        <i class="fa fa-truck"></i>
                                        <span>Antar</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="rental-details">
                        <div class="detail-item">
                            <span class="detail-label">Harga Sewa</span>
                            <span class="detail-value price">{{ $rental->formatted_price }}</span>
                            <small style="font-size: 0.7rem; color: #6c757d;">per {{ $rental->cycle_value }} Jam</small>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Denda Keterlambatan</span>
                            <span class="detail-value penalty">{{ $rental->formatted_penalties_price }}</span>
                            <small style="font-size: 0.7rem; color: #6c757d;">per {{ $rental->penalties_cycle_value }}
                                Jam</small>
                        </div>
                    </div>

                    <div class="rental-actions">
                        <!-- Tombol Detail/Lihat -->
                        <a href="{{ route('seller.rentals.show', $rental->id) }}" class="btn-icon btn-view">
                            <i class="fa fa-eye"></i> 
                        </a>
                        <!--tombol edit-->
                        <a href="{{ route('seller.rentals.edit', $rental->id) }}" class="btn-icon btn-edit">
                            <i class="fa fa-edit"></i> 
                        </a>
                        <form id="delete-form-{{ $rental->id }}" action="{{ route('seller.rentals.destroy', $rental->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button type="button" class="btn-icon btn-delete btn-delete-rental" data-id="{{ $rental->id }}" title="Hapus">
                            <i class="fa fa-trash"></i> 
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa fa-box-open"></i>
                    <h3>Belum Ada Paket Sewa</h3>
                    <p>Mulai tambahkan paket sewa untuk produk Anda</p>
                </div>
            @endforelse

            @if ($rentals->hasPages())
                <div class="pagination">
                    {{ $rentals->links() }}
                </div>
            @endif
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete Confirmation
    document.querySelectorAll('.btn-delete-rental').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const form = document.getElementById('delete-form-' + id);

            Swal.fire({
                title: 'Hapus Paket Sewa?',
                text: 'Paket sewa akan dihapus permanen. Lanjutkan?',
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
