@extends('kurir.layouts.master')

@section('navbar')
    @include('kurir.layouts.navbar')
@endsection
@section('navbot')
@include('kurir.layouts.navbot')
@endsection
@section('content')
<div class="pb-5">
    <!-- Filter Tabs -->
    <div class="px-3 py-2 bg-white shadow-sm sticky-top" style="z-index: 90;">
        <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none;">
            <button class="tab-btn active px-4 py-2 rounded-pill border-0 small fw-bold" data-status="all">
                Semua
            </button>
            <button class="tab-btn px-4 py-2 rounded-pill border-0 small fw-bold" data-status="pickup">
                Perlu Diambil
            </button>
            <button class="tab-btn px-4 py-2 rounded-pill border-0 small fw-bold" data-status="delivery">
                Sedang Dikirim
            </button>
            <button class="tab-btn px-4 py-2 rounded-pill border-0 small fw-bold" data-status="completed">
                Selesai
            </button>
        </div>
    </div>

    <!-- Orders List -->
    <div class="orders-container px-3 py-3">
        @if($orders->count() > 0)
        @foreach($orders as $order)
        @php
        // Get delivery shipment for this order
        // Get delivery shipment for this order (prioritize latest)
        $shipment = $order->shipments->sortByDesc('created_at')->first();
        $shipmentStatus = $shipment ? $shipment->status : 'unknown';

        // Determine colors based on status
        $statusColor = 'secondary';
        $statusText = 'Unknown';
        $statusIcon = 'fa-question';

        if($shipmentStatus === 'pending') {
        $statusColor = 'warning';
        $statusText = $shipment && !$shipment->courier_id ? 'Permintaan Baru (Pool)' : 'Menunggu Konfirmasi';
        $statusIcon = 'fa-clock';
        } elseif($shipmentStatus === 'assigned') {
        $statusColor = 'primary';
        $statusText = 'Perlu Diambil';
        $statusIcon = 'fa-box';
        } elseif($shipmentStatus === 'picked_up') {
        $statusColor = 'info';
        $statusText = 'Sudah Diambil';
        $statusIcon = 'fa-check';
        } elseif($shipmentStatus === 'on_the_way') {
        $statusColor = 'info';
        $statusText = 'Dalam Perjalanan';
        $statusIcon = 'fa-motorcycle';
        } elseif($shipmentStatus === 'arrived') {
        $statusColor = 'success';
        $statusText = 'Sudah Sampai';
        $statusIcon = 'fa-map-marker-alt';
        }
        @endphp

        <div class="card-custom mb-4 order-card" data-status="{{ $shipmentStatus }}">
            <div class="card-body p-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-{{ $statusColor }} bg-opacity-10 p-2 rounded-circle me-2">
                            <i class="fa {{ $statusIcon }} text-{{ $statusColor }}"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">#{{ $order->order_code }}</h6>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $shipment && $shipment->assigned_at ? \Carbon\Carbon::parse($shipment->assigned_at)->format('d M Y, H:i') : '-' }}
                            </small>
                        </div>
                    </div>
                    <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} rounded-pill px-3 py-2">
                        {{ $statusText }}
                    </span>
                </div>

                <!-- Info -->
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-user text-secondary me-2" style="width: 16px;"></i>
                        <span class="text-dark small fw-medium">{{ $order->user->name }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-phone text-secondary me-2" style="width: 16px;"></i>
                        <span class="text-dark small">{{ $order->user->phone }}</span>
                    </div>
                    <div class="d-flex align-items-start">
                        <i class="fa fa-map-marker-alt text-secondary me-2 mt-1" style="width: 16px;"></i>
                        <span class="text-dark small text-truncate-2">
                            {{ $shipment->delivery_address_snapshot ?? 'Alamat tidak tersedia' }}
                        </span>
                    </div>
                </div>

                @if($shipment && $shipment->courier_notes)
                <div class="alert alert-warning border-0 py-2 px-3 mb-3 d-flex align-items-center">
                    <i class="fa fa-sticky-note text-warning me-2"></i>
                    <small class="text-dark">{{ $shipment->courier_notes }}</small>
                </div>
                @endif

                <!-- Actions -->
                <div class="d-grid gap-2">
                    @if($shipmentStatus === 'pending')
                    {{-- Pending: Show Accept and Reject buttons --}}
                    <div class="row g-2">
                        <div class="col-7">
                            <form action="{{ route('kurir.orders.accept', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 rounded-3 fw-bold shadow-sm text-white">
                                    <i class="fa fa-check me-2"></i>Terima
                                </button>
                            </form>
                        </div>
                        <div class="col-5">
                            <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-3 fw-bold shadow-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal{{ $order->id }}">
                                <i class="fa fa-times me-1"></i>Tolak
                            </button>
                        </div>
                    </div>
                    @elseif($shipmentStatus === 'assigned')
                    {{-- Assigned: Courier accepted, show Pickup button --}}
                    <div class="row g-2">
                        <div class="col-12">
                            <a href="{{ route('kurir.pickup.scan', $order->id) }}" class="btn btn-primary-custom w-100 py-2 rounded-3 fw-bold shadow-sm">
                                <i class="fa fa-qrcode me-2"></i>Ambil Barang (Scan QR)
                            </a>
                        </div>
                    </div>
                    @elseif($shipmentStatus === 'picked_up' || $shipmentStatus === 'on_the_way')
                    <div class="row g-2">
                        <div class="col-{{ $shipmentStatus === 'on_the_way' ? '6' : '12' }}">
                            <a href="{{ route('kurir.map', $order->id) }}" class="btn btn-info w-100 py-2 rounded-3 fw-bold text-white shadow-sm ">
                                <i class="fa fa-map-marked-alt me-2"></i>Lihat Peta
                            </a>
                        </div>
                    </div>
                    @elseif($shipmentStatus === 'arrived')
                    <a href="{{ route('kurir.handover.options', $shipment->id) }}" class="btn btn-success w-100 py-2 rounded-3 fw-bold shadow-sm text-white">
                        <i class="fa fa-hand-holding-heart me-2"></i>Berikan Barang
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        @if($shipmentStatus === 'pending' || $shipmentStatus === 'assigned')
        <div class="modal fade" id="rejectModal{{ $order->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $order->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="rejectModalLabel{{ $order->id }}">
                            <i class="fa fa-exclamation-triangle text-warning me-2"></i>Tolak Pengiriman
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('kurir.orders.reject', $order->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Anda akan menolak pengiriman untuk order <strong>#{{ $order->order_code }}</strong>. Pesanan ini akan ditugaskan ke kurir lain.</p>

                            <div class="mb-3">
                                <label for="rejection_reason{{ $order->id }}" class="form-label fw-bold small">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea class="form-control"
                                    id="rejection_reason{{ $order->id }}"
                                    name="rejection_reason"
                                    rows="3"
                                    placeholder="Contoh: Sedang ada pengiriman lain, Lokasi terlalu jauh, dll."
                                    required></textarea>
                                <small class="text-muted">Jelaskan alasan Anda menolak pengiriman ini</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">
                                <i class="fa fa-times me-2"></i>Tolak Pengiriman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @endforeach
        @else
        <!-- Empty State -->
        <div class="empty-state text-center py-5">
            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                <i class="fa fa-box-open text-secondary fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark">Belum Ada Pesanan</h5>
            <p class="text-muted small">Pesanan pengiriman akan muncul di sini</p>
        </div>
        @endif
    </div>
</div>

<style>
    .tab-btn {
        background: #f3f4f6;
        color: #6b7280;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .tab-btn:hover {
        background: #e5e7eb;
    }

    .tab-btn.active {
        background: #22c55e;
        color: white;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

@push('scripts')
<script>
    // Tab switching with actual filtering
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');

            const status = this.dataset.status;

            // Filter cards based on shipment status
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach(card => {
                const cardStatus = card.dataset.status;

                if (status === 'all') {
                    // Show all orders
                    card.style.display = '';
                } else if (status === 'pickup' && (cardStatus === 'pending' || cardStatus === 'assigned')) {
                    // Perlu Diambil = pending (awaiting accept) or assigned (accepted)
                    card.style.display = '';
                } else if (status === 'delivery' && (cardStatus === 'picked_up' || cardStatus === 'on_the_way' || cardStatus === 'arrived')) {
                    // Sedang Dikirim = picked_up, on_the_way, arrived
                    card.style.display = '';
                } else if (status === 'completed' && cardStatus === 'arrived') {
                    // Selesai = arrived (shipment completed)
                    card.style.display = '';
                } else {
                    // Hide card
                    card.style.display = 'none';
                }
            });

            // Check if any cards are visible
            const visibleCards = Array.from(orderCards).filter(card => card.style.display !== 'none');
            const emptyState = document.querySelector('.empty-state');
            const container = document.querySelector('.orders-container');

            if (visibleCards.length === 0) {
                if (!emptyState) {
                    const empty = document.createElement('div');
                    empty.className = 'empty-state text-center py-5';
                    empty.innerHTML = `
                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fa fa-box-open text-secondary fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Tidak Ada Pesanan</h5>
                    <p class="text-muted small">Belum ada pesanan dengan filter ini</p>
                `;
                    container.appendChild(empty);
                } else {
                    emptyState.style.display = '';
                    emptyState.querySelector('h5').textContent = 'Tidak Ada Pesanan';
                    emptyState.querySelector('p').textContent = 'Belum ada pesanan dengan filter ini';
                }
            } else {
                if (emptyState) {
                    emptyState.style.display = 'none';
                }
            }
        });
    });
</script>
@endpush
@endsection