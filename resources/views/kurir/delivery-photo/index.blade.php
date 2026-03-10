@extends('kurir.layouts.master')

@section('navbar')
<div class="mobile-top-header">
    <div class="header-left">
        <a href="{{ route('kurir.dashboard') }}" class="text-dark">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="header-center">
        <h5 class="mb-0 fw-bold">Foto Bukti Pengiriman</h5>
    </div>
</div>
@endsection

@section('navbot')
    @include('kurir.layouts.navbot')
@endsection

@section('content')
<div class="container pb-5">
    <div class="text-center px-4 pt-4 pb-3">
        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="fa fa-camera text-primary" style="font-size: 40px;"></i>
        </div>
        <h5 class="fw-bold mb-2">Ambil Foto Bukti Pengiriman</h5>
        <p class="text-muted small">Seperti di Shopee/Grab, ambil foto customer bersama paket untuk menyelesaikan pengiriman</p>
    </div>

    @if($activeDeliveries->isEmpty())
        <div class="alert alert-info mx-3">
            <i class="fa fa-info-circle me-2"></i>
            Tidak ada pengiriman aktif yang perlu foto bukti saat ini.
        </div>
    @else
        <div class="px-3">
            @foreach($activeDeliveries as $shipment)
                @php
                    $order = $shipment->order;
                    $product = $order->productRental->product ?? null;
                    
                    // Get status label
                    $statusBadge = 'bg-warning text-dark';
                    $statusText = 'Dalam Perjalanan';
                    
                    if ($shipment->status === 'arrived') {
                        $statusBadge = 'bg-info text-white';
                        $statusText = 'Sudah Sampai';
                    } elseif ($shipment->status === 'on_the_way') {
                        $statusBadge = 'bg-warning text-dark';
                        $statusText = 'Dalam Perjalanan';
                    }
                @endphp
                
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                            <small class="text-muted">{{ $order->order_code }}</small>
                        </div>
                        
                        <h6 class="fw-bold mb-1">{{ $order->user->name }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="fa fa-box me-1"></i> {{ $product->name ?? 'N/A' }}
                        </p>
                        
                        <p class="text-muted small mb-3">
                            <i class="fa fa-map-marker-alt me-1"></i>
                            @if($order->address)
                                {{ $order->address->full_address }}
                            @elseif($shipment->delivery_address_snapshot)
                                {{ $shipment->delivery_address_snapshot }}
                            @else
                                Alamat tidak tersedia
                            @endif
                        </p>
                        
                        <a href="{{ route('kurir.delivery-photo.show', $shipment->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fa fa-camera me-2"></i>Ambil Foto Bukti Pengiriman
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection