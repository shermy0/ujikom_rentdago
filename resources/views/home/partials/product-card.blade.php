{{-- resources/views/home/partials/product-card.blade.php --}}

<a href="{{ route('customer.product.detail', [
    'slug' => !empty($product->shop?->slug) ? $product->shop->slug : 'no-shop',
    'product' => $product->id
]) }}" class="text-decoration-none text-dark d-block">

    <div class="card border-0 shadow-sm rounded-4 product-card d-flex flex-column">

        {{-- IMAGE --}}
        <div class="position-relative">
            @if($product->images->first())
                <img 
                    src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                    class="card-img-top rounded-top-4"
                    style="height:140px; object-fit:cover"
                >
            @else
                <div class="product-image-placeholder rounded-top-4">
                    <i class="fa fa-box"></i>
                </div>
            @endif

            <span class="product-category-badge">
                {{ $product->category->name }}
            </span>
        </div>

        {{-- BODY --}}
        <div class="card-body p-2 mt-auto product-body">
            {{-- NAMA SHOP --}}
            @if($product->shop)
                <div class="shop-name text-muted small mb-1 text-truncate">
                    {{ $product->shop->name_store }}
                </div>
            @endif

            {{-- NAMA PRODUK --}}
            <h6 class="mb-1 fw-bold text-truncate product-name">
                {{ $product->name }}
            </h6>

            {{-- HARGA --}}
            @if($product->rentals->count())
                <div class="price">
                    Mulai dari Rp{{ number_format($product->rentals->first()->price, 0, ',', '.') }}
                </div>
            @else
                <div class="text-muted small">
                    Harga belum tersedia
                </div>
            @endif

            @if($product->renter_count > 0)
    <div class="text-muted small mt-1">
        {{ $product->renter_count }} orang sudah menyewa
    </div>
@endif

        </div>
    </div>
</a>
