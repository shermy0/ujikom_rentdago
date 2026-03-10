@extends('admin.layouts.app')

@section('content')
    <div class="card">
       <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
        <i class="bi bi-cart-check me-2"></i>Daftar Produk Sewa
    </h5>

</div>

        <div class="card-body">
            <!-- Filter & Search -->
            <form action="{{ route('admin.product_sewa.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama/kode produk..."
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="shop" class="form-select">
                            <option value="">Semua Toko</option>
                            @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name_store }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="delivery" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="pickup" {{ request('delivery') == 'pickup' ? 'selected' : '' }}>Ambil Sendiri
                            </option>
                            <option value="delivery" {{ request('delivery') == 'delivery' ? 'selected' : '' }}>Antar
                            </option>
                            <option value="pickup_delivery"
                                {{ request('delivery') == 'pickup_delivery' ? 'selected' : '' }}>Keduanya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                    @if (request('search') || request('shop') || request('delivery'))
                        <div class="col-md-12">
                            <a href="{{ route('admin.product_sewa.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg me-1"></i>Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Produk</th>
                            <th>Toko</th>
                            <th>Harga Sewa</th>
                            <th>Denda</th>
                            <th>Metode Pengambilan</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentals as $index => $rental)
                            <tr>
                                <td>{{ $rentals->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($rental->product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $rental->product->images->first()->image_path) }}"
                                                alt="{{ $rental->product->name }}" class="product-thumb me-2">
                                        @else
                                            <div class="product-thumb-placeholder me-2">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $rental->product->name }}</strong>
                                            <br><small class="text-muted">{{ $rental->product->code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-secondary">{{ $rental->product->shop->name_store ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">{{ $rental->formatted_price }}</span>
                                    <br><small class="text-muted">/ {{ $rental->cycle_value }} Jam</small>
                                </td>
                                <td>
                                    <span class="text-danger fw-bold">{{ $rental->formatted_penalties_price }}</span>
                                    <br><small class="text-muted">/ {{ $rental->penalties_cycle_value }} Jam</small>
                                </td>
                                <td>
                                    @php
                                        // Get raw value from database, bypassing the accessor
                                        $deliveryMethod = $rental->getRawOriginal('is_delivery');
                                    @endphp
                                    
                                    @if (!empty($deliveryMethod))
                                        @if ($deliveryMethod == 'pickup')
                                            <span class="badge bg-primary"><i class="bi bi-person-walking"></i> Ambil Sendiri</span>
                                        @elseif ($deliveryMethod == 'delivery')
                                            <span class="badge bg-warning"><i class="bi bi-truck"></i> Antar</span>
                                        @elseif ($deliveryMethod == 'pickup_delivery')
                                            <span class="badge bg-primary me-1"><i class="bi bi-person-walking"></i> Ambil Sendiri</span>
                                            <span class="badge bg-warning"><i class="bi bi-truck"></i> Antar</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.product_sewa.show', $rental->id) }}"
                                            class="btn btn-outline-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    Belum ada data produk sewa
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($rentals->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $rentals->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            .bg-purple {
                background-color: #7b1fa2 !important;
            }

            .product-thumb {
                width: 45px;
                height: 45px;
                object-fit: cover;
                border-radius: 8px;
            }

            .product-thumb-placeholder {
                width: 45px;
                height: 45px;
                background: #f8f9fa;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #adb5bd;
            }
        </style>
    @endpush

@endsection
