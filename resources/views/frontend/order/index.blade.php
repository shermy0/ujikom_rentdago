@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/order.css') }}">
<link rel="stylesheet" href="{{ asset('frontend/assets/css/protail.css') }}?v={{ time() }}">

    {{-- HEADER DETAIL PRODUK --}}
    <div class="product-detail-header">
        <a href="{{ url()->previous() }}" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Pesanan Saya</div>
        <div class="header-spacer"></div>
    </div>

<div class="order-container">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @forelse($orders as $order)
        <div class="order-item">
            <div class="order-item-header">
                <div class="order-code">{{ $order->order_code }}</div>
                
                <span class="status-badge {{ $order->status }}">
                    @if($order->status === 'pending')
                        <i class="fa fa-clock"></i> Pending
                    @elseif($order->status === 'confirmed')
                        <i class="fa fa-check-circle"></i> Dikonfirmasi
                    @elseif($order->status === 'ongoing')
                        <i class="fa fa-spinner"></i> Berlangsung
                    @elseif($order->status === 'penalty')
                        <i class="fa fa-triangle-exclamation"></i> Denda Belum Dibayar
                    @elseif($order->status === 'completed')
                        <i class="fa fa-check"></i> Selesai
                    @else
                        <i class="fa fa-times-circle"></i> Dibatalkan
                    @endif
                </span>
            </div>
            
                {{-- Status tambahan --}}
            @if($order->status === 'ongoing' && $order->isLate())
                <div class="order-status-badge late">
                    <i class="fa fa-clock"></i> Terlambat
                </div>
            @endif


            <div class="order-item-body">
                <div class="product-image-small">
                    @if($order->productRental->product->images->first())
                        <img src="{{ asset('storage/' . $order->productRental->product->images->first()->image_path) }}" 
                             alt="{{ $order->productRental->product->name }}">
                    @else
                        <div class="no-image-small">
                            <i class="fa fa-image"></i>
                        </div>
                    @endif
                </div>

                <div class="order-item-info">
                    <h6>{{ $order->productRental->product->name }}</h6>
                    <p class="order-date">
                        <i class="fa fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}
                    </p>
                    <p class="order-price">
                        Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="order-item-footer">
                @if(($order->payment?->payment_status === 'unpaid') && $order->status !== 'cancelled')
                    <a href="{{ route('customer.order.payment', $order->id) }}" class="btn-small primary">
                        <i class="fa fa-credit-card"></i> Bayar
                    </a>
                @endif
                
                <a href="{{ route('customer.order.show', $order->id) }}" class="btn-small secondary">
                    <i class="fa fa-eye"></i> Detail
                </a>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fa fa-inbox"></i>
            <p>Belum ada pesanan</p>
        </div>
    @endforelse

    <div class="pagination-wrapper">
        {{ $orders->links() }}
    </div>

</div>

@endsection