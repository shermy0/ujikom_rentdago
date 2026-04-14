@extends('frontend.masterseller')

@section('content')
<style>
    .products-container {
        background: #f5f5f5;
        min-height: 100vh;
        padding: 0;
    }
    
    .products-header-bar {
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
    
    .products-header-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .products-header-back a {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
    }
    
    .products-header-title {
        flex: 1;
        text-align: center;
        color: #fff;
        font-size: 18px;
        font-weight: 600;
    }
    
    .products-header-add {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .products-header-add a {
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
    
    .filter-row {
        display: flex;
        gap: 0.5rem;
    }
    
    .filter-select {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        background: #fff;
    }
    
    .products-list {
        padding: 0 1rem 5rem 1rem;
    }

.product-card {
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    
    display: flex;
    gap: 1rem;
    position: relative; /* penting buat positioning */
}
    
    .product-image {
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
    
.product-details {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
    
    .product-code {
        font-size: 0.75rem;
        color: #007bff;
        margin-bottom: 0.25rem;
    }
    
    .product-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .product-category {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .product-meta {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.available {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.maintenance {
        background: #fff3cd;
        color: #856404;
    }
    
.product-actions {
    position: absolute;
    bottom: 10px;
    right: 10px;

    display: flex;
    gap: 0.4rem;
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

    .alert-danger {
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
</style>

<div class="products-container">
            <!-- Header -->
    <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.dashboard.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Daftar Produk
        </div>

        <div class="create-header-spacer"></div>
                        <div class="products-header-add">
            <a href="{{ route('seller.products.create') }}">
                <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-section">
        <form action="{{ route('seller.products.index') }}" method="GET">
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" 
                       name="search" 
                       placeholder="Cari nama atau kode barang..." 
                       value="{{ request('search') }}">
            </div>
            
            <div class="filter-row">
                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" 
                                {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                        Tersedia
                    </option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                        Maintenance
                    </option>
                </select>
            </div>
        </form>
    </div>

    <!-- Products List -->
    <div class="products-list">
        @forelse($products as $product)
            <div class="product-card">
                @if($product->images->count() > 0)
                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                         alt="{{ $product->name }}"
                         class="product-image">
                @else
                    <div class="product-image">
                        <i class="fa fa-camera"></i>
                    </div>
                @endif

                <div class="product-details">
                    <div class="product-name">{{ $product->name }}</div>
                    <div class="product-code">{{ $product->code }}</div>
                    <div class="product-category">
                        <i class="fa fa-tag"></i> {{ $product->category->name }}
                    </div>
                    <div class="product-meta">
                        <span class="status-badge {{ $product->is_maintenance ? 'maintenance' : 'available' }}">
                            {{ $product->is_maintenance ? 'Maintenance' : 'Tersedia' }}
                        </span>
                    </div>
                </div>

                <div class="product-actions">
                    <a href="{{ route('seller.products.show', $product->id) }}" 
                    class="btn-icon btn-view"
                    title="Lihat Detail">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('seller.products.edit', $product->id) }}" 
                    class="btn-icon btn-edit"
                    title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{ route('seller.products.destroy', $product->id) }}" 
                        method="POST" 
                        onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon btn-delete" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa fa-box-open"></i>
                <h3>Belum Ada Barang</h3>
                <p>Mulai tambahkan barang untuk disewakan</p>
            </div>
        @endforelse

        @if($products->hasPages())
            <div class="pagination">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection