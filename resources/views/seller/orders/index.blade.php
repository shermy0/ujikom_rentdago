@extends('frontend.masterseller')

@section('content')
<style>
    .orders-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .page-header h2 {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }
    
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .filter-tabs {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .filter-tab {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .filter-tab:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    .filter-tab.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .order-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .order-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .order-code {
        font-weight: 600;
        color: #1a1a1a;
        font-size: 16px;
    }
    
    .order-date {
        color: #6b7280;
        font-size: 14px;
        margin-top: 4px;
    }
    
    .order-badges {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .order-body {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .product-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }
    
    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .order-details {
        flex: 1;
    }
    
    .product-title {
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
        font-size: 16px;
    }
    
    .rental-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
        color: #6b7280;
        font-size: 14px;
    }
    
    .rental-info span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .rental-info i {
        width: 16px;
        color: #9ca3af;
    }
    
    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }
    
    .customer-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 14px;
    }
    
    .order-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .order-amount {
        font-weight: 600;
        color: #1a1a1a;
        font-size: 18px;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-ongoing {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-completed {
        background: #e5e7eb;
        color: #374151;
    }
    
    .status-penalty {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .delivery-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .delivery-delivery {
        background: #fef3c7;
        color: #92400e;
    }
    
    .delivery-pickup {
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .shipment-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .shipment-rejected {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }
    
    .shipment-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
    
    .payment-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
    }
    
    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 16px;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 16px;
    }
    
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }
    
    .action-btn {
        padding: 8px 16px;
        border-radius: 8px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.2s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .action-btn:hover {
        background: #2563eb;
        color: white;
        text-decoration: none;
    }
    
    .action-btn-outline {
        background: white;
        color: #3b82f6;
        border: 1px solid #3b82f6;
    }
    
    .action-btn-outline:hover {
        background: #eff6ff;
        color: #3b82f6;
    }

    .pagination-wrapper .pagination {
        display: flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination-wrapper .page-item {
        display: inline-block;
    }

    .pagination-wrapper .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 8px 12px;
        border-radius: 8px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    .pagination-wrapper .page-link:hover {
        background: #f9fafb;
        color: #3b82f6;
        border-color: #3b82f6;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .pagination-wrapper .page-item.disabled .page-link {
        background: #f3f4f6;
        color: #d1d5db;
        border-color: #e5e7eb;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-wrapper .page-link svg {
        width: 20px;
        height: 20px;
    }

    .pagination-wrapper .page-item:first-child .page-link,
    .pagination-wrapper .page-item:last-child .page-link {
        padding: 8px 16px;
    }

    @media (max-width: 640px) {
        .pagination-wrapper .pagination {
            gap: 4px;
        }
        
        .pagination-wrapper .page-link {
            min-width: 36px;
            height: 36px;
            padding: 6px 10px;
            font-size: 13px;
        }
        
        .pagination-wrapper .page-item:not(.active):not(:first-child):not(:last-child) {
            display: none;
        }
        
        .pagination-wrapper .page-item.active,
        .pagination-wrapper .page-item.active + .page-item,
        .pagination-wrapper .page-item:has(+ .page-item.active) {
            display: inline-block;
        }
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
            Semua Pesanan
        </div>
        <div class="create-header-spacer"></div>
    </div>


<div class="orders-container">

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-tabs">
            <button class="filter-tab active" data-status="confirmed">
                <i class="fas fa-check-circle"></i> Dikonfirmasi
            </button>
            <button class="filter-tab active" data-status="penalty">
                <i class="fas fa-exclamation-triangle"></i> Denda
            </button>
            <button class="filter-tab" data-status="ongoing">
                <i class="fas fa-clock"></i> Berlangsung
            </button>
            <button class="filter-tab" data-status="completed">
                <i class="fas fa-check-double"></i> Selesai
            </button>
        </div>
    </div>

    <!-- Orders List -->
    <div class="orders-list">
        @forelse($orders as $order)
            <div class="order-item" data-status="{{ $order->status }}" data-delivery="{{ $order->delivery_method }}">
                <!-- Order Header -->
                <div class="order-header">
                    <div>
                        <div class="order-code">Order #{{ $order->order_code }}</div>
                        <div class="order-date">
                            <i class="fas fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}
                        </div>
                    </div>
                    <div class="order-badges">
                        <span class="status-badge status-{{ $order->status }}">
                            @if($order->status == 'confirmed')
                                <i class="fas fa-check-circle"></i> Dikonfirmasi
                            @elseif($order->status == 'ongoing')
                                <i class="fas fa-clock"></i> Berlangsung
                            @elseif($order->status == 'completed')
                                <i class="fas fa-check-double"></i> Selesai
                            @elseif($order->status == 'penalty')
                                <i class="fas fa-exclamation-triangle"></i> Denda
                            @endif
                        </span>
                        
                        <span class="delivery-badge delivery-{{ $order->delivery_method }}">
                            @if($order->delivery_method == 'delivery')
                                <i class="fas fa-truck"></i> Antar
                            @else
                                <i class="fas fa-store"></i> Ambil Sendiri
                            @endif
                        </span>
                        
                        <span class="payment-badge payment-paid">
                            <i class="fas fa-check-circle"></i> Sudah Dibayar
                        </span>
                        
                        {{-- Shipment status badges removed - use Kurir menu for courier management --}}
                    </div>
                </div>

                <!-- Order Body -->
                <div class="order-body">
                    <div class="product-img">
                        @if($order->productRental->product->images->first())
                            <img src="{{ asset('storage/' . $order->productRental->product->images->first()->image_path) }}" 
                                 alt="{{ $order->productRental->product->name }}">
                        @else
                            📷
                        @endif
                    </div>
                    
                    <div class="order-details">
                        <div class="product-title">
                            {{ $order->productRental->product->name }}
                        </div>
                        <div class="rental-info">
<span>
    <i class="fas fa-calendar-check"></i>
    Mulai: {{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}
</span>
<span>
    <i class="fas fa-calendar-times"></i>
    Selesai: {{ \Carbon\Carbon::parse($order->end_time)->format('d M Y, H:i') }}
</span>
<span>
    <i class="fas fa-clock"></i>
    Durasi: {{ $order->productRental->duration }} {{ $order->productRental->cycle_value }}
</span>

                        </div>
                    </div>
                </div>

                <!-- Order Footer -->
                <div class="order-footer">
                    <div class="customer-info">
                        <i class="fas fa-user"></i>
                        <span>{{ $order->user->name }}</span>
                    </div>
                    <div class="order-right">
                        <div class="order-amount">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </div>
                        <a href="{{ route('seller.orders.show', $order->id) }}" class="action-btn">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Belum ada pesanan</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="pagination-wrapper">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<script>
// ✅ DEFAULT: Hanya confirmed dan penalty yang aktif
let activeFilters = ['confirmed', 'penalty'];

// Apply initial filters on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 Seller Orders Page: Loaded');
    
    // Apply filters first
    applyFilters();
    
    // Setup filter tabs click handlers
    setupFilterTabs();
    
    // Then mark orders as read (if jQuery is available)
    if (typeof $ !== 'undefined') {
        markAllOrdersAsRead();
        
        // Hide badge immediately
        if (window.SellerOrderBadge) {
            window.SellerOrderBadge.updateBadge(0);
        }
        
        // Setup individual order click handler
        setupOrderClickHandler();
    }
});

/**
 * Setup filter tabs functionality
 */
function setupFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const status = this.dataset.status;
            console.log('Filter clicked:', status);
            
            // Toggle filter
            if (activeFilters.includes(status)) {
                // Jangan allow menghapus semua filter
                if (activeFilters.length > 1) {
                    activeFilters = activeFilters.filter(f => f !== status);
                    this.classList.remove('active');
                }
            } else {
                activeFilters.push(status);
                this.classList.add('active');
            }
            
            // Apply filters
            applyFilters();
        });
    });
}

/**
 * Apply filters to order items
 */
function applyFilters() {
    const orders = document.querySelectorAll('.order-item');
    const emptyState = document.querySelector('.empty-state');
    let visibleCount = 0;
    
    orders.forEach(order => {
        const status = order.dataset.status;
        
        if (activeFilters.includes(status)) {
            order.style.display = 'block';
            visibleCount++;
        } else {
            order.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    if (emptyState) {
        if (visibleCount === 0 && orders.length > 0) {
            // Ada data tapi semua terfilter
            emptyState.style.display = 'block';
            emptyState.querySelector('p').textContent = 'Tidak ada pesanan dengan filter yang dipilih';
        } else if (orders.length === 0) {
            // Memang tidak ada data sama sekali
            emptyState.style.display = 'block';
            emptyState.querySelector('p').textContent = 'Belum ada pesanan';
        } else {
            // Ada data yang tampil
            emptyState.style.display = 'none';
        }
    }
}

/**
 * Mark all orders as read (requires jQuery)
 */
function markAllOrdersAsRead() {
    console.log('📝 Marking all orders as read...');
    
    $.ajax({
        url: '/seller/api/orders/mark-read',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('✅ Orders marked as read:', response);
            
            // Update badge to 0
            if (window.SellerOrderBadge) {
                window.SellerOrderBadge.updateBadge(0);
            }
        },
        error: function(xhr) {
            console.error('❌ Error marking orders as read:', xhr);
        }
    });
}

/**
 * Setup individual order click handler (requires jQuery)
 */
function setupOrderClickHandler() {
    $(document).on('click', '.order-card, .order-item, [data-order-id]', function(e) {
        // Don't trigger if clicking on action button
        if ($(e.target).closest('.action-btn').length) {
            return;
        }
        
        const orderId = $(this).data('order-id');
        
        if (orderId) {
            console.log('📝 Marking order', orderId, 'as read');
            
            $.ajax({
                url: '/seller/api/orders/' + orderId + '/mark-read',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('✅ Order marked as read:', response);
                },
                error: function(xhr) {
                    console.error('❌ Error marking order as read:', xhr);
                }
            });
        }
    });
}
</script>
@endsection