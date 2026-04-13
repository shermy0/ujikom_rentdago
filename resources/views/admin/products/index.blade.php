@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-box-seam me-2"></i>Daftar Barang
            </h5>

        </div>

        <div class="card-body">
            <!-- Filter & Search -->
            <form action="{{ route('admin.products.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama/kode barang..."
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia
                            </option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                                Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                    @if (request('search') || request('category') || request('status'))
                        <div class="col-md-12">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg me-1"></i>Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive product-table-scroll">
                <table class="table table-hover align-middle product-table text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td><code>{{ $product->code }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="product-icon me-2">
                                            <i class="bi {{ $product->category->icon ?? 'bi-box' }}"></i>
                                        </div>
                                        <strong>{{ $product->name }}</strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-info">{{ $product->category->name }}</span></td>
                                <td>
                                    @if ($product->is_maintenance)
                                        <span class="badge bg-danger">Maintenance</span>
                                    @else
                                        <span class="badge bg-success">Tersedia</span>
                                    @endif
                                </td>
                                <td>{{ $product->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.products.show', $product->id) }}"
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
                                    Belum ada data barang
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->withQueryString()->links() }}
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

            .product-icon {
                width: 32px;
                height: 32px;
                background: linear-gradient(135deg, #ee4d2d, #ff6b35);
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 14px;
            }

            code {
                color: #666;
                background: #f8f9fa;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
            }

            .product-table-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-x;
            }

            .product-table {
                min-width: 900px;
            }

            @media (max-width: 768px) {
                .card-header,
                .card-body {
                    padding: 14px;
                }
            }
        </style>
    @endpush
@endsection
