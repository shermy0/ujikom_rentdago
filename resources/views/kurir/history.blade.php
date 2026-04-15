@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection

@section('navbot')
    @include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="pb-5">
    <!-- Summary Card -->
    <div class="px-3 pt-3">
        <div class="summary-card">
            <h6 class="summary-label">Total Pengiriman</h6>
            <div class="row text-center">
                <div class="col-4">
                    <h3 class="summary-number">{{ $todayCount }}</h3>
                    <small class="summary-sub">Hari Ini</small>
                </div>
                <div class="col-4 border-start border-end border-white border-opacity-25">
                    <h3 class="summary-number">{{ $weekCount }}</h3>
                    <small class="summary-sub">Minggu Ini</small>
                </div>
                <div class="col-4">
                    <h3 class="summary-number">{{ $monthCount }}</h3>
                    <small class="summary-sub">Bulan Ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="px-3 mb-3">
        <div class="d-flex gap-2">
            <button class="filter-btn active flex-fill py-2 rounded-3 border-0 small fw-bold" data-filter="all">
                Semua
            </button>
            <button class="filter-btn flex-fill py-2 rounded-3 border-0 small fw-bold" data-filter="today">
                Hari Ini
            </button>
            <button class="filter-btn flex-fill py-2 rounded-3 border-0 small fw-bold" data-filter="week">
                Minggu Ini
            </button>
        </div>
    </div>

    <!-- History List -->
    <div class="px-3" id="historyList">
        @forelse($shipments as $shipment)
            @php
                $order = $shipment->order;
                $product = $order->productRental->product ?? null;
                $shop = $product->shop ?? null;
                $deliveredAt = $shipment->updated_at;
                
                // Determine filter class
                $filterClass = 'history-item';
                if ($deliveredAt->isToday()) {
                    $filterClass .= ' filter-today filter-week';
                } elseif ($deliveredAt->isCurrentWeek()) {
                    $filterClass .= ' filter-week';
                }
            @endphp
            
            <div class="card-custom mb-3 {{ $filterClass }}" data-date="{{ $deliveredAt->timestamp }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">#{{ $order->order_code }}</h6>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $deliveredAt->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                            </small>
                        </div>
                        
                        {{-- Status Badge --}}
                        <span class="badge rounded-pill px-3
                            @if($shipment->status === 'delivered') bg-success bg-opacity-10 text-success
                            @elseif($shipment->status === 'failed') bg-danger bg-opacity-10 text-danger
                            @elseif($shipment->status === 'returned') bg-warning bg-opacity-10 text-warning
                            @elseif($shipment->status === 'rejected') bg-secondary bg-opacity-10 text-secondary
                            @endif">
                            <i class="fa 
                                @if($shipment->status === 'delivered') fa-check-circle
                                @elseif($shipment->status === 'failed') fa-times-circle
                                @elseif($shipment->status === 'returned') fa-rotate-left
                                @elseif($shipment->status === 'rejected') fa-ban
                                @endif me-1"></i>
                            {{ \App\Models\Shipment::getStatusLabel($shipment->status) }}
                        </span>
                    </div>
                    
                    @if($product && $shop)
                    <div class="bg-light rounded-3 p-2 mb-2">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fa fa-box text-success me-2" style="width: 14px;"></i>
                            <span class="text-dark small">{{ Str::limit($product->name, 30) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="fa fa-store text-success me-2" style="width: 14px;"></i>
                            <span class="text-dark small">{{ $shop->name_store }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fa fa-map-marker-alt text-success me-2" style="width: 14px;"></i>
                            <span class="text-dark small">{{ Str::limit($order->address->address ?? 'Alamat tidak tersedia', 40) }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Rejection Reason (only for rejected status) --}}
                    @if($shipment->status === 'rejected' && $shipment->rejection_reason)
                    <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                        <small class="text-danger d-block mb-1 fw-bold" style="font-size: 10px;">
                            <i class="fa fa-exclamation-triangle me-1"></i>Alasan Penolakan:
                        </small>
                        <small class="text-dark">{{ $shipment->rejection_reason }}</small>
                    </div>
                    @endif

                    {{-- Courier Notes (for failed status) --}}
                    @if($shipment->status === 'failed' && $shipment->courier_notes)
                    <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                        <small class="text-danger d-block mb-1 fw-bold" style="font-size: 10px;">
                            <i class="fa fa-info-circle me-1"></i>Catatan Kegagalan:
                        </small>
                        <small class="text-dark">{{ $shipment->courier_notes }}</small>
                    </div>
                    @endif

                    {{-- Delivery Proof Image (only for delivered status) --}}
                    @if($shipment->status === 'delivered' && $shipment->delivery_proof_photo)
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1" style="font-size: 10px;">
                            <i class="fa fa-camera me-1"></i>Bukti Pengiriman:
                        </small>
                        <img src="{{ asset($shipment->delivery_proof_photo) }}" 
                             alt="Proof" 
                             class="img-fluid rounded-3" 
                             style="max-height: 120px; cursor: pointer;"
                             onclick="showImageModal(this.src)">
                    </div>
                    @endif

                    {{-- General Notes --}}
                    @if($shipment->courier_notes && $shipment->status === 'delivered')
                    @php
                        $normalizedNotes = str_replace(
                            ['Diverifikasi via PHOTO', 'Serah terima ke customer diverifikasi via FOTO kurir'],
                            'Verifikasi serah terima: FOTO kurir',
                            $shipment->courier_notes
                        );
                        $noteLines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $normalizedNotes))));
                        $displayNotes = implode("\n", array_unique($noteLines));
                    @endphp
                    <div class="mt-2 p-2 bg-light rounded-3">
                        <small class="text-muted d-block mb-1" style="font-size: 10px;">
                            <i class="fa fa-sticky-note me-1"></i>Catatan:
                        </small>
                        <small class="text-dark">{{ $displayNotes }}</small>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="text-center py-5" id="emptyState">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                    <i class="fa fa-clipboard-list text-secondary fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">Belum Ada Riwayat</h5>
                <p class="text-muted small">Riwayat pengiriman akan muncul di sini</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" 
                        data-bs-dismiss="modal" style="z-index: 1;"></button>
                <img src="" id="modalImage" class="img-fluid rounded-3 w-100">
            </div>
        </div>
    </div>
</div>

<style>
    .summary-card {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border-radius: 16px;
        padding: 20px;
        color: white;
        margin-bottom: 16px;
    }

    .summary-label {
        font-size: 13px;
        margin-bottom: 16px;
        opacity: 0.8;
    }

    .summary-number {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .summary-sub {
        font-size: 11px;
        opacity: 0.75;
    }

    .filter-btn {
        background: #f3f4f6;
        color: #6b7280;
        transition: all 0.3s;
    }
    
    .filter-btn:hover {
        background: #e5e7eb;
    }
    
    .filter-btn.active {
        background: #22c55e;
        color: white;
    }

    .history-item {
        transition: all 0.3s;
    }

    .history-item.d-none-filter {
        display: none !important;
    }

    .card-custom {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
</style>

@push('scripts')
<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');
        
        // Filter items
        const filter = this.dataset.filter;
        const items = document.querySelectorAll('.history-item');
        const emptyState = document.getElementById('emptyState');
        let visibleCount = 0;
        
        items.forEach(item => {
            if (filter === 'all') {
                item.classList.remove('d-none-filter');
                visibleCount++;
            } else if (filter === 'today' && item.classList.contains('filter-today')) {
                item.classList.remove('d-none-filter');
                visibleCount++;
            } else if (filter === 'week' && item.classList.contains('filter-week')) {
                item.classList.remove('d-none-filter');
                visibleCount++;
            } else {
                item.classList.add('d-none-filter');
            }
        });
        
        // Show/hide empty state
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });
});

// Image modal
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endpush
@endsection