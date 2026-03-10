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
                        <th>Parent</th>
                        <th>Sub-Kategori</th>
                        <th>Dibuat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($category->icon)
                                    <img src="{{ asset('storage/' . $category->icon) }}" alt="{{ $category->name }}" class="me-2" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover;">
                                @endif
                                <strong>{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>
                            @if($category->parent)
                                <span class="badge bg-info">{{ $category->parent->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($category->children->count() > 0)
                                <span class="badge bg-secondary">{{ $category->children->count() }} sub</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $category->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.categories.edit', $category) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
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
    .category-icon {
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
</style>
@endpush
@endsection
