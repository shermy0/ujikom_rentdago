@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-tags me-2"></i>Daftar Kategori
        </h5>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </a>
    </div>
    <div class="card-body">
        <!-- Filter & Search -->
        <form action="{{ route('admin.categories.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Cari nama kategori..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="parent" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="root" {{ request('parent') == 'root' ? 'selected' : '' }}>Kategori Utama</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent') == $parent->id ? 'selected' : '' }}>
                            Sub dari: {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
            @if(request('search') || request('parent'))
            <div class="col-md-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100">
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
                        <th>Nama Kategori</th>
                        <th>Slug</th>
                        <th style="width: 120px;">Dibuat</th>
                        <th style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    {{-- Parent Category Row --}}
                    <tr class="parent-category-row" data-category-id="{{ $category->id }}">
                        <td>{{ $category->id }}</td>
                        <td>
                            <div class="d-flex align-items-start">
                                @if($category->children->count() > 0)
                                    <button class="btn btn-sm btn-link p-0 me-2 toggle-children"
                                            data-category-id="{{ $category->id }}"
                                            style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #666; margin-top: 10px;"
                                            title="Buka/Tutup sub-kategori">
                                        <i class="bi bi-chevron-down transition-transform" style="font-size: 18px; font-weight: bold;"></i>
                                    </button>
                                @else
                                    <div style="width: 30px; margin-right: 8px;"></div>
                                @endif
                                @if($category->icon)
                                    <img src="{{ asset('storage/' . $category->icon) }}"
                                         alt="{{ $category->name }}"
                                         class="me-3 flex-shrink-0"
                                         style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;">
                                @else
                                    <div class="category-icon me-3 flex-shrink-0">
                                        <i class="bi bi-tag"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <strong style="font-size: 15px;">{{ $category->name }}</strong>
                                        @if($category->children->count() > 0)
                                            <span class="badge bg-secondary" style="font-size: 11px; padding: 4px 9px;">
                                                {{ $category->children->count() }} sub
                                            </span>
                                        @endif
                                    </div>
                                    @if(is_null($category->parent_id))
                                        <span class="badge bg-gradient-primary" style="font-size: 11px; padding: 5px 10px;">
                                            <i class="bi bi-star-fill"></i> Kategori Utama
                                        </span>
                                    @else
                                        <span class="badge bg-info text-white" style="font-size: 11px; padding: 5px 10px;">
                                            <i class="bi bi-arrow-return-right"></i> Sub: {{ $category->parent->name ?? '-' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>{{ $category->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center"
                                   style="width: 38px; height: 38px;"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($category->products_count > 0 || $category->children->count() > 0)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center"
                                            style="width: 38px; height: 38px;"
                                            title="Tidak bisa dihapus ({{ $category->children->count() > 0 ? 'Memiliki sub-kategori' : 'Sedang digunakan' }})"
                                            disabled>
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                @else
                                    <form action="{{ route('admin.categories.destroy', $category) }}"
                                          method="POST"
                                          class="m-0"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center"
                                                style="width: 38px; height: 38px;"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Child Categories Rows --}}
                    @foreach($category->children as $child)
                    <tr class="child-category-row" data-parent-id="{{ $category->id }}">
                        <td class="text-muted">{{ $child->id }}</td>
                        <td>
                            <div class="d-flex align-items-start" style="padding-left: 40px;">
                                <i class="bi bi-arrow-return-right text-muted me-3 flex-shrink-0" style="font-size: 20px; margin-top: 15px;"></i>
                                @if($child->icon)
                                    <img src="{{ asset('storage/' . $child->icon) }}"
                                         alt="{{ $child->name }}"
                                         class="me-3 flex-shrink-0"
                                         style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;">
                                @else
                                    <div class="category-icon me-3 flex-shrink-0" style="width: 45px; height: 45px; font-size: 20px;">
                                        <i class="bi bi-tag"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <strong style="font-size: 14px;">{{ $child->name }}</strong>
                                    <br>
                                    {{-- <small class="text-muted">Sub kategori dari: <span class="text-primary">{{ $category->name }}</span></small> --}}
                                </div>
                            </div>
                        </td>
                        <td><code style="font-size: 11px;">{{ $child->slug }}</code></td>
                        <td class="text-muted" style="font-size: 13px;">{{ $child->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.categories.edit', $child) }}"
                                   class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center"
                                   style="width: 38px; height: 38px;"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($child->products_count > 0)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center"
                                            style="width: 38px; height: 38px;"
                                            title="Tidak bisa dihapus (Sedang digunakan)"
                                            disabled>
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                @else
                                    <form action="{{ route('admin.categories.destroy', $child) }}"
                                          method="POST"
                                          class="m-0"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center"
                                                style="width: 38px; height: 38px;"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            Belum ada data kategori
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($categories->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $categories->withQueryString()->links() }}
        </div>
        @endif
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
    .bg-gradient-primary {
        background: linear-gradient(135deg, #ee4d2d, #ff6b35) !important;
        border: none;
    }
    .table th {
        font-weight: 600;
        color: #666;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
        padding: 16px 12px;
    }
    .badge {
        font-weight: 500;
        padding: 4px 8px;
        font-size: 11px;
    }
    .category-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #ee4d2d, #ff6b35);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }
    code {
        color: #666;
        background: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: 'Courier New', monospace;
    }
    .btn-outline-primary, .btn-outline-secondary, .btn-outline-danger {
        border-width: 1px;
        transition: all 0.2s ease;
    }
    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-1px);
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
        transform: translateY(-1px);
    }
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
        transform: translateY(-1px);
    }
    .btn-outline-secondary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .text-muted small {
        font-size: 12px;
    }
    .fw-semibold {
        font-weight: 600;
    }
    .parent-category-row {
        background-color: #ffffff;
    }
    .child-category-row {
        background-color: #f8f9fa;
    }
    .child-category-row:hover {
        background-color: #e9ecef;
    }
    .parent-category-row:hover {
        background-color: #f8f9fa;
    }
    .toggle-children {
        transition: all 0.3s ease;
    }
    .toggle-children.collapsed i {
        transform: rotate(-90deg);
    }
    .child-category-row {
        transition: all 0.3s ease;
    }
    .child-category-row.hidden {
        display: none;
    }
    .transition-transform {
        transition: transform 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';

    window.addEventListener('load', function() {
        console.log('Category toggle initializing...');

        const table = document.querySelector('table tbody');
        if (!table) {
            console.error('Table not found');
            return;
        }

        // Event delegation on table
        table.addEventListener('click', function(e) {
            const button = e.target.closest('.toggle-children');
            if (!button) return;

            e.preventDefault();
            e.stopPropagation();

            const categoryId = button.getAttribute('data-category-id');
            const icon = button.querySelector('i');

            console.log('Toggling category:', categoryId);

            // Find all child rows
            const allRows = table.querySelectorAll('tr');
            allRows.forEach(function(row) {
                if (row.getAttribute('data-parent-id') === categoryId) {
                    // Toggle visibility
                    if (row.style.display === 'none') {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });

            // Toggle icon rotation
            if (icon.style.transform === 'rotate(-90deg)') {
                icon.style.transform = 'rotate(0deg)';
            } else {
                icon.style.transform = 'rotate(-90deg)';
            }
        });

        console.log('Toggle initialized successfully');
    });
})();
</script>
@endpush
@endsection
