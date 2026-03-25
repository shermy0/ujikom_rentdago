
@extends('frontend.masterseller')

@section('content')
<style>
    .product-detail-container {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 0;
    }
    
    .image-gallery {
        background: #fff;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .main-image {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 1rem;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .main-image.no-image {
        color: #6c757d;
        font-size: 4rem;
    }
    
    .thumbnail-gallery {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    
    .thumbnail-gallery::-webkit-scrollbar {
        height: 4px;
    }
    
    .thumbnail-gallery::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 2px;
    }
    
    .thumbnail {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    
    .thumbnail.active,
    .thumbnail:hover {
        border-color: #770C0C;
    }
    
    .detail-section {
        background: #fff;
        padding: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .detail-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .detail-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        width: 120px;
        font-weight: 500;
        color: #666;
        flex-shrink: 0;
    }
    
    .detail-value {
        flex: 1;
        color: #333;
        word-wrap: break-word;
    }
    
    .product-code-badge {
        background: #770C0C;
        color: #fff;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-family: monospace;
        font-size: 0.95rem;
        display: inline-block;
    }
    
    .status-badge-large {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-badge-large.available {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge-large.maintenance {
        background: #fff3cd;
        color: #856404;
    }
/* 
    .btn-qr-large {
        background: #28a745;
        color: #fff;
    }

    .btn-qr-large:hover {
        background: #218838;
    }
     */
    .description-text {
        line-height: 1.6;
        color: #555;
        white-space: pre-wrap;
    }
    
    .empty-description {
        color: #999;
        font-style: italic;
    }
    
    .category-badge {
        background: #ffe7e7;
        color: #770C0C;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* QR Code Section */
    .qr-code-section {
        background: #fff;
        padding: 1.5rem;
        margin-bottom: 0.5rem;
        text-align: center;
    }

    .qr-code-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 12px;
        margin-top: 1rem;
    }

    .qr-code-image {
        width: 250px;
        height: 250px;
        padding: 1rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .qr-info-text {
        color: #666;
        font-size: 0.9rem;
        text-align: center;
        max-width: 300px;
    }

    .qr-download-btn {
        background: #28a745;
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .qr-download-btn:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
            .detail-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
                .content-section {
            padding: 1rem;
        }

</style>

<div class="product-detail-container">
    <!-- Header -->
        <div class="create-header-bar">
        <div class="create-header-back">
            <a href="{{ route('seller.products.index') }}">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>
        <div class="create-header-title">
            Detail Produk
        </div>
        <div class="create-header-spacer"></div>
    </div>
        <div class="content-section">

    <!-- Image Gallery -->
    <div class="detail-card">
    <div class="image-gallery">
        @if($product->images->count() > 0)
            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                 alt="{{ $product->name }}"
                 class="main-image"
                 id="mainImage">
            
            @if($product->images->count() > 1)
                <div class="thumbnail-gallery">
                    @foreach($product->images as $index => $image)
                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                             alt="Thumbnail {{ $index + 1 }}"
                             class="thumbnail {{ $index === 0 ? 'active' : '' }}"
                             onclick="changeMainImage(this)">
                    @endforeach
                </div>
            @endif
        @else
            <div class="main-image no-image">
                <i class="fa fa-camera"></i>
            </div>
        @endif
    </div>
    </div>

    <!-- Basic Info -->
        <div class="detail-card">
    <div class="detail-section">
        <div class="detail-section-title">Informasi Dasar</div>
        
        <div class="detail-row">
            <div class="detail-label">Kode Barang:</div>
            <div class="detail-value">
                <span class="product-code-badge">{{ $product->code }}</span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Nama Barang:</div>
            <div class="detail-value">{{ $product->name }}</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Kategori:</div>
            <div class="detail-value">
                <span class="category-badge">
                    <i class="fa fa-tag"></i>
                    {{ $product->category->name }}
                </span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">
                <span class="status-badge-large {{ $product->is_maintenance ? 'maintenance' : 'available' }}">
                    <i class="fa fa-{{ $product->is_maintenance ? 'wrench' : 'check-circle' }}"></i>
                    {{ $product->is_maintenance ? 'Sedang Maintenance' : 'Tersedia' }}
                </span>
            </div>
        </div>
    </div>
        </div>

    <!-- Description -->
        <div class="detail-card">
    <div class="detail-section">
        <div class="detail-section-title">Deskripsi</div>
        @if($product->description)
            <div class="description-text">{{ $product->description }}</div>
        @else
            <div class="empty-description">Tidak ada deskripsi</div>
        @endif
    </div>

    <!-- Condition -->
    <div class="detail-section">
        <div class="detail-section-title">Kondisi Barang</div>
        @if($product->condition)
            <div class="description-text">{{ $product->condition }}</div>
        @else
            <div class="empty-description">Tidak ada informasi kondisi</div>
        @endif
    </div>

    <!-- Additional Info -->
    <div class="detail-section">
        <div class="detail-section-title">Informasi Tambahan</div>
        
        <div class="detail-row">
            <div class="detail-label">Jumlah Foto:</div>
            <div class="detail-value">{{ $product->images->count() }} foto</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Dibuat:</div>
            <div class="detail-value">{{ $product->created_at->format('d M Y, H:i') }}</div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Terakhir Diubah:</div>
            <div class="detail-value">{{ $product->updated_at->format('d M Y, H:i') }}</div>
        </div>
    </div>
        </div>
        </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('seller.products.edit', $product->id) }}" 
           class="btn-large-action btn-large-edit">
            <i class="fa fa-edit"></i>
            Edit
        </a>
        
        <form action="{{ route('seller.products.destroy', $product->id) }}" 
              method="POST" 
              onsubmit="return confirm('Yakin ingin menghapus barang ini? Tindakan ini tidak dapat dibatalkan.')"
              style="flex: 1;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-large-action btn-large-delete" style="width: 100%;">
                <i class="fa fa-trash"></i>
                Hapus
            </button>
        </form>
    </div>
</div>

<script>
function changeMainImage(thumbnail) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = thumbnail.src;
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}
</script>
@endsection
