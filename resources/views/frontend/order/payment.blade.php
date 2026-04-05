@extends('frontend.master')

@section('navbar')
    @include('frontend.navbar')
@endsection
@section('navbot')
    @include('frontend.navbot')
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('frontend/assets/css/order.css') }}">

<div class="order-container">

    <div class="order-header">
        <a href="{{ route('customer.order.index') }}" class="header-back">
            <i class="fa fa-arrow-left"></i>
        </a>
        <div class="header-title">Pembayaran</div>
        <div style="width:40px;"></div>
    </div>

    <div class="order-card">
        <div class="order-status-badge pending">
            <i class="fa fa-clock"></i>
            Menunggu Pembayaran
        </div>

        <div class="order-code">
            Order: <strong>{{ $order->order_code }}</strong>
        </div>

        <div class="order-product">
            <div class="product-image">
                @if($order->productRental->product->images->first())
                    <img src="{{ asset('storage/' . $order->productRental->product->images->first()->image_path) }}" 
                         alt="{{ $order->productRental->product->name }}">
                @else
                    <div class="no-image">
                        <i class="fa fa-image"></i>
                    </div>
                @endif
            </div>

            <div class="product-info">
                <h5>{{ $order->productRental->product->name }}</h5>
                <p class="rental-duration">
                    <i class="fa fa-clock"></i>
                    {{ $order->productRental->cycle_value }} Jam
                </p>
                <p class="rental-time">
                    <i class="fa fa-calendar"></i>
                    {{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}
                    <i class="fa fa-arrow-right"></i>
                    {{ \Carbon\Carbon::parse($order->end_time)->format('d M Y, H:i') }}
                </p>
                <p class="rental-delivery">
                    @if($order->productRental->is_delivery === 'pickup')
                        <i class="fa fa-location-dot"></i> Pickup
                    @elseif($order->productRental->is_delivery === 'delivery')
                        <i class="fa fa-truck"></i> Delivery
                    @else
                        <i class="fa fa-circle-check"></i> Pickup / Delivery
                    @endif
                </p>
            </div>
        </div>

        <div class="order-summary">
            <div class="summary-row">
                <span>Harga Sewa</span>
                <strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-row total">
                <span>Total Pembayaran</span>
                <strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>

        <button id="pay-button" class="pay-button">
            <i class="fa fa-credit-card"></i>
            Bayar Sekarang
        </button>

        <form action="{{ route('customer.order.cancel', $order->id) }}" method="POST" style="margin-top: 10px;">
            @csrf
            <button type="submit" class="cancel-button" onclick="return confirm('Yakin ingin membatalkan order?')">
                Batalkan Order
            </button>
        </form>
    </div>

</div>

{{-- Midtrans Snap JS --}}
{{-- Midtrans Snap JS --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
const payButton = document.getElementById('pay-button');
const orderId = '{{ $order->id }}';
const regenerateUrl = '{{ route("customer.order.regenerate-token", ":id") }}'.replace(':id', orderId);

// Function to reset button state
function resetButton() {
    payButton.disabled = false;
    payButton.innerHTML = '<i class="fa fa-credit-card"></i> Bayar Sekarang';
    console.log('✅ Button reset - ready for next payment attempt');
}

// Function to set loading state
function setLoadingState() {
    payButton.disabled = true;
    payButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
    console.log('⏳ Loading payment...');
}

payButton.addEventListener('click', async function () {
    console.log('🔵 Payment button clicked');
    setLoadingState();

    try {
        console.log('📡 Requesting new payment token from:', regenerateUrl);
        
        const response = await fetch(regenerateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        console.log('📥 Response status:', response.status);
        const data = await response.json();
        console.log('📦 Response data:', data);

        if (!data.success) {
            console.error('❌ Token generation failed:', data.message);
            alert(data.message || 'Terjadi kesalahan saat memproses pembayaran');
            resetButton();
            return;
        }

        if (!data.snap_token) {
            console.error('❌ No snap_token in response');
            alert('Token pembayaran tidak valid, silakan coba lagi');
            resetButton();
            return;
        }

        console.log('✅ Token received, opening Midtrans popup...');
        console.log('🔑 Snap Token:', data.snap_token.substring(0, 20) + '...');

        // Check if snap is available
        if (typeof snap === 'undefined') {
            console.error('❌ Midtrans Snap.js not loaded');
            alert('Sistem pembayaran belum siap, silakan refresh halaman');
            resetButton();
            return;
        }

        snap.pay(data.snap_token, {
            onSuccess: function(result) {
                console.log('✅ Payment SUCCESS:', result);
                window.location.href = '{{ route("customer.order.payment.finish") }}?order_id={{ $order->order_code }}';
            },
            onPending: function(result) {
                console.log('⏳ Payment PENDING:', result);
                window.location.href = '{{ route("customer.order.show", $order->id) }}';
            },
            onError: function(result) {
                console.error('❌ Payment ERROR:', result);
                alert('Pembayaran gagal, silakan coba lagi');
                resetButton();
            },
            onClose: function() {
                console.log('🔴 Payment popup CLOSED by user');
                console.log('🔄 Resetting button - user can retry payment');
                resetButton();
            }
        });

    } catch (error) {
        console.error('❌ Exception caught:', error);
        alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        resetButton();
    }
});

// Log when page loads
console.log('💳 Payment page loaded');
console.log('📋 Order ID:', orderId);
console.log('🔗 Regenerate URL:', regenerateUrl);
</script>

@endsection