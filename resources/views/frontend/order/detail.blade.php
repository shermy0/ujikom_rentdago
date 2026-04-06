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
        <div class="header-title">Detail Pesanan</div>
        <div style="width:40px;"></div>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> {{ session('info') }}
    </div>
    @endif

    {{-- Delivery Status Messages (Requested) --}}
    @php
    $shipment = $order->deliveryShipment;
    $shipmentStatus = $shipment->status ?? null;
    @endphp

    @if($order->status !== 'cancelled' && $order->status !== 'pending' && $order->status !== 'completed')
    <div class="delivery-status-card">
        {{-- LOGIC UNTUK DELIVERY --}}
        @if($order->delivery_method === 'delivery')
        @if($order->status === 'confirmed' && (!$shipmentStatus || $shipmentStatus === 'pending' || $shipmentStatus === 'assigned' || $shipmentStatus === 'picked_up'))
        <div class="status-content">
            <div class="status-icon pulse-blue"><i class="fa fa-box"></i></div>
            <div class="status-text">
                <h6>Barang Sedang Disiapkan</h6>
                <p>Penjual sedang menyiapkan barang Anda.</p>
            </div>
        </div>
        @elseif($shipmentStatus === 'pending')
        <div class="status-content">
            <div class="status-icon pulse-green"><i class="fa fa-truck-fast"></i></div>
            <div class="status-text">
                <h6>Pesanan Menunggu Penjemputan</h6>
                <p>Kurir sedang menuju lokasi penjemputan Anda.</p>
            </div>
        </div>
    </div>
    @elseif($shipmentStatus === 'on_the_way')
    <div class="status-content">
        <div class="status-icon pulse-green"><i class="fa fa-truck-fast"></i></div>
        <div class="status-text">
            <h6>Pesanan Sedang Dikirim</h6>
            <p>Kurir sedang dalam perjalanan menuju lokasi Anda.</p>
        </div>
    </div>
    @elseif($shipmentStatus === 'arrived')
    <div class="status-content">
        <div class="status-icon arrived-icon"><i class="fa fa-location-dot"></i></div>
        <div class="status-text">
            <h6>Kurir Sudah Sampai</h6>
            <p>Silakan temui kurir dan siapkan kode verifikasi di bawah.</p>
        </div>
    </div>
    @endif

    @endif
</div>
@endif

<div class="order-card">

    {{-- Status Badge --}}
    <div class="order-status-badge {{ $order->status }}">
        @if($order->status === 'pending')
        <i class="fa fa-clock"></i> Menunggu Pembayaran
        @elseif($order->status === 'confirmed')
        <i class="fa fa-check-circle"></i> Pesanan Dikonfirmasi
        @elseif($order->status === 'ongoing')
        <i class="fa fa-spinner"></i> Sedang Berlangsung
        @elseif($order->status === 'penalty')
        <i class="fa fa-triangle-exclamation"></i> Denda Belum Dibayar
        @elseif($order->status === 'completed')
        <i class="fa fa-check"></i> Selesai
        @else
        <i class="fa fa-times-circle"></i> Dibatalkan
        @endif
    </div>

    {{-- Status Tambahan untuk Terlambat --}}
    @if($order->status === 'ongoing' && $order->isLate())
    <div class="order-status-badge late">
        <i class="fa fa-clock"></i> Terlambat
    </div>
    @endif

    {{-- Order Code --}}
    <div class="order-code">
        Order: <strong>{{ $order->order_code }}</strong>
    </div>

    {{-- Product Info --}}
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
                Durasi: {{ $order->productRental->cycle_value }} Jam
            </p>
        </div>
    </div>

    {{-- Alert untuk Order Dibatalkan --}}
    @if($order->status === 'cancelled')
    <div class="alert alert-warning">
        <i class="fa fa-info-circle"></i>
        <div>
            <strong>Order Dibatalkan</strong>
            <p style="margin: 5px 0 0 0; font-size: 13px;">
                Order ini telah dibatalkan {{ $order->updated_at ? 'pada ' . \Carbon\Carbon::parse($order->updated_at)->format('d M Y, H:i') : '' }}
            </p>
        </div>
    </div>
    @endif

    {{-- QR Code & OTP - Hanya untuk confirmed/ongoing --}}
    @if(in_array($order->status, ['confirmed','ongoing']))
    <div class="detail-section qr-section">


        @if($order->delivery_method !== 'delivery')
        <h6 style="margin-top: 20px;"><i class="fa fa-qrcode"></i> QR Verifikasi</h6>
        <div style="text-align:center; margin-top:10px">
            <div style="display: inline-block; background: white; padding: 8px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                @if($order->qr_code && file_exists(storage_path('app/public/' . $order->qr_code)))
                    <img src="{{ asset('storage/'.$order->qr_code) }}"
                        alt="QR Code"
                        style="width:180px; height:180px; display:block;">
                @else
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($order->order_code) !!}
                @endif
            </div>
            <p style="font-size:12px; margin-top:8px; color:#666;">
                Tunjukkan QR ini ke Penjual
            </p>
        </div>
        @endif
    </div>
    @endif

    {{-- Periode Sewa Aktif --}}
    @if($order->start_time && $order->end_time && in_array($order->status, ['confirmed', 'ongoing', 'completed']))
    <div class="detail-section">
        <h6><i class="fa fa-calendar-check"></i> Periode Sewa Aktif</h6>
        <div class="detail-row">
            <span>Mulai</span>
            <strong>{{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}</strong>
        </div>
        <div class="detail-row">
            <span>Selesai</span>
            <strong>{{ \Carbon\Carbon::parse($order->end_time)->format('d M Y, H:i') }}</strong>
        </div>
    </div>
    @endif

    {{-- Jadwal yang Dibatalkan --}}
    @if($order->status === 'cancelled' && $order->start_time && $order->end_time)
    <div class="detail-section">
        <h6><i class="fa fa-calendar-times"></i> Jadwal Sewa (Dibatalkan)</h6>
        <div class="detail-row">
            <span>Jadwal Mulai</span>
            <strong>{{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}</strong>
        </div>
        <div class="detail-row">
            <span>Jadwal Selesai</span>
            <strong>{{ \Carbon\Carbon::parse($order->end_time)->format('d M Y, H:i') }}</strong>
        </div>
    </div>
    @endif

    {{-- Countdown --}}
    @if($order->status === 'ongoing' && $order->start_time && $order->end_time)
    <div class="countdown-box">
        <div class="countdown-icon">
            <i class="fa fa-hourglass-half"></i>
        </div>
        <div class="countdown-content">
            <div class="countdown-label">Sisa Waktu Sewa</div>
            <div class="countdown-time"
                data-end-time="{{ $order->end_time->format('Y-m-d H:i:s') }}">
                Menghitung...
            </div>
            <div class="countdown-started">
                Dimulai: {{ \Carbon\Carbon::parse($order->start_time)->format('d M Y, H:i') }}
            </div>
        </div>
    </div>
    @endif

    {{-- Delivery Info --}}
    <div class="detail-section">
        <h6><i class="fa fa-truck"></i> Informasi Pengambilan</h6>

        <div class="detail-row">
            <span>Metode</span>
            <strong>
                @if($order->delivery_method === 'pickup')
                <i class="fa fa-location-dot"></i> Pickup di Lokasi
                @elseif($order->delivery_method === 'delivery')
                <i class="fa fa-truck"></i> Antar ke Lokasi
                @else
                <i class="fa fa-circle-check"></i>
                @if($order->productRental->is_delivery === 'pickup')
                Pickup di Lokasi
                @elseif($order->productRental->is_delivery === 'delivery')
                Antar ke Lokasi
                @else
                Pickup / Delivery
                @endif
                @endif
            </strong>
        </div>

        {{-- PICKUP --}}
        @if(($order->delivery_method === 'pickup' || !$order->delivery_method) && $order->productRental->pickup_address)
        <div class="detail-row">
            <span>Lokasi Pickup</span>
            <strong>{{ $order->productRental->pickup_address }}</strong>
        </div>
        @endif

        {{-- DELIVERY --}}
        @if($order->delivery_method === 'delivery' && $order->delivery_address_snapshot)
        @php
        $parts = explode('Catatan:', $order->delivery_address_snapshot, 2);
        $addressText = trim($parts[0]);
        $noteText = $parts[1] ?? null;
        @endphp

        <div class="detail-row align-start">
            <span>Alamat Pengiriman</span>
            <div class="detail-value text-right">
                <strong>{{ $addressText }}</strong>

                @if($noteText)
                <div class="delivery-note">
                    Catatan: {{ trim($noteText) }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- PICKUP STATUS CONTROLS (Moved from top) --}}
        @if($order->delivery_method === 'pickup' && $order->status !== 'ongoing' && $order->status !== 'completed' && $order->status !== 'cancelled')
        @if(!$shipmentStatus || $shipmentStatus === 'pending')
        <div class="status-content" style="display:flex; flex-direction:column; width:100%; gap:15px; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 12px; border: 1px dashed #ced4da;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div class="status-icon pulse-blue"><i class="fa fa-store"></i></div>
                <div class="status-text">
                    <h6>Siap Dijemput</h6>
                    <p>Silakan menuju ke lokasi toko untuk pengambilan.</p>
                </div>
            </div>
            {{-- TOMBOL MULAI --}}
            <form action="{{ route('customer.order.pickup.start', $order->id) }}" method="POST" style="width:100%">
                @csrf
                <button type="submit" class="btn-primary" style="width:100%; padding:12px; border-radius:10px; border:none; background:#3b82f6; color:white; font-weight:bold;">
                    <i class="fa fa-person-walking"></i> Mulai Pengambilan
                </button>
            </form>
        </div>
        @elseif($shipmentStatus === 'on_the_way')
        <div id="pickupTrackingContainer" class="status-content" style="display:flex; flex-direction:column; width:100%; gap:15px; margin-top: 15px; padding: 15px; background: #f0fff4; border-radius: 12px; border: 1px solid #c6f6d5;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div class="status-icon pulse-green"><i class="fa fa-person-walking"></i></div>
                <div class="status-text">
                    <h6>Sedang Menuju Toko</h6>
                    <p id="distanceText">Menghitung jarak...</p>
                </div>
            </div>

            {{-- GPS STATUS INDICATOR --}}
            <div id="gpsStatus" style="display:flex; align-items:center; gap:8px; padding:8px; background:rgba(59, 130, 246, 0.1); border-radius:8px; font-size:12px;">
                <i class="fa fa-satellite-dish" style="color:#3b82f6;"></i>
                <span style="color:#3b82f6; font-weight:600;">GPS Aktif</span>
            </div>

            {{-- DYNAMIC ARRIVAL BUTTON (Hidden by default, shown when distance <= 20m) --}}
            <div id="arrivalButtonContainer" style="display:none; width:100%;">
                <button
                    id="btnArrival"
                    type="button"
                    onclick="confirmArrival()"
                    class="btn-success"
                    style="width:100%; padding:14px; border-radius:10px; border:none; background:#22c55e; color:white; font-weight:bold; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); transition: all 0.3s;">
                    <i class="fa fa-check-circle"></i> Sudah Sampai
                </button>
            </div>
        </div>
        @elseif($shipmentStatus === 'arrived')
        <div class="status-content" style="margin-top: 15px; padding: 15px; background: #fff5f5; border-radius: 12px; border: 1px solid #fecaca;">
            <div class="status-icon arrived-icon"><i class="fa fa-store"></i></div>
            <div class="status-text">
                <h6>Anda Sudah di Toko</h6>
                <p>Silakan tunjukkan QR Code di bawah ke penjual.</p>
            </div>
        </div>
        @endif
        @endif

        {{-- MAP TRACKING --}}
        @if(isset($mapData) && !in_array($order->status, ['ongoing', 'completed', 'cancelled']))
        <div id="map" style="height: 350px; width: 100%; margin-top: 15px; border-radius: 12px; z-index: 1; border: 1px solid #ddd;"></div>
        @endif
    </div>

    {{-- Shop Info --}}
    @if($order->productRental->product->shop)
    <div class="detail-section">
        <h6><i class="fa fa-store"></i> Informasi Toko</h6>
        <div class="detail-row">
            <span>Nama Toko</span>
            <strong>{{ $order->productRental->product->shop->name_store }}</strong>
        </div>
        <div class="detail-row">
            <span>Alamat Toko</span>
            <strong>{{ $order->productRental->product->shop->address_store }}</strong>
        </div>
    </div>
    @endif

    {{-- Payment Info --}}
    <div class="detail-section">
        <h6><i class="fa fa-money-bill"></i> Informasi Pembayaran</h6>
        <div class="detail-row">
            <span>Status Pembayaran</span>
            <span class="payment-badge {{ $order->payment?->payment_status ?? 'unpaid' }}">
                @if($order->payment?->payment_status === 'paid')
                <i class="fa fa-check-circle"></i> Lunas
                @elseif($order->payment?->payment_status === 'refunded')
                <i class="fa fa-undo"></i> Dikembalikan
                @else
                <i class="fa fa-clock"></i> Belum Dibayar
                @endif
            </span>
        </div>

        @if($order->payment?->paid_at)
        <div class="detail-row">
            <span>Dibayar pada</span>
            <strong>{{ \Carbon\Carbon::parse($order->payment->paid_at)->format('d M Y, H:i') }}</strong>
        </div>
        @endif
    </div>

    {{-- Price Summary --}}
    <div class="order-summary">
        <div class="summary-row">
            <span>Harga Sewa</span>
            <strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <strong>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</strong>
        </div>
    </div>

    {{-- PENALTY INFO --}}
    @if($order->orderReturn)
    <div class="detail-section">
        <h6><i class="fa fa-triangle-exclamation"></i> Denda Keterlambatan</h6>

        <div class="detail-row">
            <span>Status</span>
            <span class="payment-badge {{ $order->orderReturn->payment_status }}">
                @if($order->orderReturn->payment_status === 'paid')
                <i class="fa fa-check-circle"></i> Sudah Dibayar
                @else
                <i class="fa fa-clock"></i> Belum Dibayar
                @endif
            </span>
        </div>

        <div class="detail-row">
            <span>Jumlah Denda</span>
            <strong style="color:#dc3545">
                Rp {{ number_format($order->orderReturn->penalties_amount, 0, ',', '.') }}
            </strong>
        </div>
    </div>
    @endif

    {{-- PENALTY CTA --}}
    @if($order->orderReturn && $order->orderReturn->payment_status === 'unpaid')
    <div class="penalty-cta">
        <a href="{{ route('customer.penalty.pay', $order->orderReturn->id) }}"
            class="penalty-btn">
            <i class="fa fa-credit-card"></i>
            Bayar Denda
        </a>
    </div>
    @endif

    {{-- Action Buttons --}}
    @if($order->status === 'pending' && $order->payment?->payment_status === 'unpaid')
    <a href="{{ route('customer.order.payment', $order->id) }}" class="pay-button">
        <i class="fa fa-credit-card"></i>
        Bayar Sekarang
    </a>

    <form action="{{ route('customer.order.cancel', $order->id) }}" method="POST" style="margin-top: 10px;">
        @csrf
        <button type="submit" class="cancel-button" onclick="return confirm('Yakin ingin membatalkan order?')">
            <i class="fa fa-times"></i> Batalkan Order
        </button>
    </form>
    @endif

    {{-- Return Feature Removed --}}

</div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countdownElement = document.querySelector('.countdown-time');

        if (countdownElement) {
            function updateCountdown() {
                const endTime = new Date(countdownElement.getAttribute('data-end-time')).getTime();
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    countdownElement.innerHTML = '<span style="color: #dc3545; font-weight: 700;">Waktu sewa telah habis</span>';
                    countdownElement.parentElement.parentElement.classList.add('expired');
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let html = '<div class="countdown-digits">';

                if (days > 0) {
                    html += `<div class="digit-group"><span class="digit">${days}</span><span class="digit-label">Hari</span></div>`;
                }

                html += `
                <div class="digit-group"><span class="digit">${hours.toString().padStart(2, '0')}</span><span class="digit-label">Jam</span></div>
                <div class="digit-separator">:</div>
                <div class="digit-group"><span class="digit">${minutes.toString().padStart(2, '0')}</span><span class="digit-label">Menit</span></div>
                <div class="digit-separator">:</div>
                <div class="digit-group"><span class="digit">${seconds.toString().padStart(2, '0')}</span><span class="digit-label">Detik</span></div>
            `;

                html += '</div>';
                countdownElement.innerHTML = html;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    });
</script>

<style>
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert i {
        font-size: 20px;
        margin-top: 2px;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    /* Delivery Status Card Styles */
    .delivery-status-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #edf2f7;
    }

    .status-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .status-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .pulse-blue {
        background: #ebf8ff;
        color: #3182ce;
        animation: pulse-blue 2s infinite;
    }

    .pulse-green {
        background: #f0fff4;
        color: #38a169;
        animation: pulse-green 2s infinite;
    }

    .arrived-icon {
        background: #fff5f5;
        color: #e53e3e;
    }

    .status-text h6 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
        font-size: 16px;
    }

    .status-text p {
        margin: 2px 0 0 0;
        font-size: 13px;
        color: #718096;
    }

    @keyframes pulse-blue {
        0% {
            box-shadow: 0 0 0 0 rgba(49, 130, 206, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(49, 130, 206, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(49, 130, 206, 0);
        }
    }

    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(56, 161, 105, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(56, 161, 105, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(56, 161, 105, 0);
        }
    }

    .qr-section {
        text-align: center;
    }

    .countdown-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .countdown-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .countdown-content {
        flex: 1;
    }

    .countdown-label {
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .countdown-time {
        color: white;
    }

    .countdown-started {
        color: rgba(255, 255, 255, 0.8);
        font-size: 11px;
        margin-top: 8px;
    }

    .countdown-digits {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .digit-group {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .digit {
        font-size: 28px;
        font-weight: 700;
        color: white;
        line-height: 1;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 12px;
        border-radius: 8px;
        min-width: 50px;
        text-align: center;
    }

    .digit-label {
        font-size: 10px;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .digit-separator {
        font-size: 28px;
        font-weight: 700;
        color: white;
        padding: 0 4px;
    }

    .countdown-box.expired {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .cancel-button {
        width: 100%;
        padding: 14px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .cancel-button:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    @media (max-width: 768px) {
        .countdown-box {
            flex-direction: column;
            text-align: center;
        }

        .digit {
            font-size: 24px;
            min-width: 45px;
            padding: 6px 10px;
        }

        .digit-separator {
            font-size: 24px;
        }
    }

    /* Fade-in animation for arrival button */
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<script>
    /**
     * Confirm Arrival - Called when "Sudah Sampai" button is clicked
     * Submits current GPS coordinates to backend for validation
     */
    /**
     * Confirm Arrival - Fast Version
     * Prioritizes SPEED over accuracy for "Sudah Sampai" action
     */
    function confirmArrival() {
        if (!navigator.geolocation) {
            alert('GPS tidak tersedia!');
            return;
        }

        const btn = document.getElementById('btnArrival');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
        }

        // OPTION A: FAST GPS
        // 1. maximumAge: 300000 (Use cached position from last 5 mins if available) -> INSTANT
        // 2. enableHighAccuracy: false (Don't wait for satellite lock) -> FAST
        // 3. timeout: 5000 (Don't wait too long)
        const options = {
            enableHighAccuracy: false,
            timeout: 5000,
            maximumAge: 300000
        };

        const successCallback = (position) => {
            const currentLat = position.coords.latitude;
            const currentLng = position.coords.longitude;

            // Create hidden form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("customer.order.pickup.arrive", $order->id) }}';
            form.innerHTML = `
                 @csrf
                 <input type="hidden" name="current_lat" value="${currentLat}">
                 <input type="hidden" name="current_lng" value="${currentLng}">
             `;
            document.body.appendChild(form);
            form.submit();
        };

        const errorCallback = (error) => {
            console.warn("GPS Fast Error:", error);
            // Fallback: If cached/low accuracy fails, try standard request
            // But usually this shouldn't happen if permissions are granted.

            // If strictly needed, we can force submit without location or ask user to retry
            // For now, alert and reset button
            alert('Gagal mendapatkan lokasi. Pastikan GPS aktif.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check-circle"></i> Sudah Sampai';
            }
        };

        navigator.geolocation.getCurrentPosition(successCallback, errorCallback, options);
    }

    if (navigator.geolocation) {
        // Prefetch location permission/cache silently on load
        navigator.geolocation.getCurrentPosition(() => {}, () => {}, {
            maximumAge: 300000,
            timeout: 1000,
            enableHighAccuracy: false
        });
    }
</script>

@if(isset($mapData))
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="https://unpkg.com/lrm-mapbox@1.2.0/dist/lrm-mapbox.min.js"></script>

<style>
    .custom-marker {
        position: relative;
    }

    .marker-pin {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        position: relative;
    }

    .marker-pin img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .marker-arrow {
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 10px solid #fff;
        filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.3));
    }

    .leaflet-routing-container {
        display: none !important;
    }

    .tracking-control {
        background: white;
        padding: 8px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-size: 11px;
        font-weight: 600;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapData = @json($mapData);
        const orderId = "{{ $order->id }}";
        const csrfToken = '{{ csrf_token() }}';

        let map, routingControl, movingMarker, staticMarker;
        let pollTimer = null;

        function createCustomMarker(imageUrl, color) {
            return L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-pin" style="border-color: ${color}"><img src="${imageUrl}" onerror="this.src='https://ui-avatars.com/api/?name=User&background=random'"></div><div class="marker-arrow" style="border-top-color: ${color}"></div>`,
                iconSize: [50, 60],
                iconAnchor: [25, 60],
                popupAnchor: [0, -65]
            });
        }

        function initMap() {
            const isDelivery = mapData.delivery_method === 'delivery';

            // 1. Initial Map View
            const startLat = isDelivery ? (mapData.courier.lat) : (mapData.customer.lat);
            const startLng = isDelivery ? (mapData.courier.lng) : (mapData.customer.lng);

            map = L.map('map').setView([startLat, startLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // 2. Setup Markers
            if (isDelivery) {
                // Courier is moving, Customer is static destination
                const courierIcon = createCustomMarker(mapData.courier.image, '#3b82f6');
                const customerIcon = createCustomMarker(mapData.customer.image, '#22c55e');

                movingMarker = L.marker([mapData.courier.lat, mapData.courier.lng], {
                        icon: courierIcon,
                        zIndexOffset: 1000
                    })
                    .addTo(map).bindPopup(`<b>🚚 Kurir: ${mapData.courier.name}</b>`);

                staticMarker = L.marker([mapData.customer.lat, mapData.customer.lng], {
                        icon: customerIcon
                    })
                    .addTo(map).bindPopup(`<b>📍 Lokasi Anda</b>`);
            } else {
                // Customer is moving (pickup), Shop is static destination
                const shopIcon = createCustomMarker(mapData.shop.image, '#3b82f6');
                const customerIcon = createCustomMarker(mapData.customer.image, '#22c55e');

                movingMarker = L.marker([mapData.customer.lat, mapData.customer.lng], {
                        icon: customerIcon,
                        zIndexOffset: 1000
                    })
                    .addTo(map).bindPopup(`<b>👤 ${mapData.customer.name} (Saya)</b>`);

                staticMarker = L.marker([mapData.shop.lat, mapData.shop.lng], {
                        icon: shopIcon
                    })
                    .addTo(map).bindPopup(`<b>🏪 ${mapData.shop.name}</b>`);
            }

            // 3. Setup Routing
            // PRODUKSI: Menggunakan Mapbox (Token dari config)
            const mapboxApiKey = "{{ config('services.mapbox.token') }}" || 'pk.eyJ1IjoiYXJ0aHVyLWlhIiwiYSI6ImNsMG05Z3Z6ZTAwN2YzaW56a3Z6N3Z6a3YifQ.Q1Z6-Q1Z6-Q1Z6';

            routingControl = L.Routing.control({
                waypoints: [movingMarker.getLatLng(), staticMarker.getLatLng()],
                router: L.Routing.mapbox(mapboxApiKey), // Gunakan Mapbox Router
                lineOptions: {
                    styles: [{
                        color: '#667eea',
                        weight: 6,
                        opacity: 1
                    }]
                },
                createMarker: () => null,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true
            }).addTo(map);

            // Fit bounds to show both markers
            const group = new L.featureGroup([movingMarker, staticMarker]);
            map.fitBounds(group.getBounds().pad(0.1));

            // 4. Polling for Delivery
            if (isDelivery && mapData.shipment && mapData.shipment.is_tracking_active) {
                startPolling();
                addTrackingBadge("📡 Live Tracking Aktif");
            }

            // 5. GPS Tracking for Pickup with Distance-Based Button Logic
            if (!isDelivery && mapData.shipment && mapData.shipment.is_tracking_active) {
                startPickupGPSTracking();
            }
        }

        /**
         * GPS Tracking Engine for Pickup Mode
         * - Continuously tracks customer location
         * - Calculates distance to shop
         * - Auto-shows/hides arrival button based on threshold
         */
        function startPickupGPSTracking() {
            let watchId = null;
            let lastUpdateTime = 0;
            const UPDATE_INTERVAL = 3000; // 3 seconds
            const csrfToken = '{{ csrf_token() }}';

            console.log('🚀 Starting Pickup GPS Tracking');

            // Check GPS availability
            if (!navigator.geolocation) {
                showGPSError('GPS tidak tersedia di perangkat Anda.');
                return;
            }

            // Update GPS status to "searching"
            updateGPSStatus('🔍 Mencari lokasi GPS...', '#f59e0b');

            watchId = navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const now = Date.now();

                    // Update marker position immediately
                    if (movingMarker) {
                        const newPos = L.latLng(lat, lng);
                        movingMarker.setLatLng(newPos);

                        // Update route
                        if (routingControl) {
                            routingControl.setWaypoints([newPos, staticMarker.getLatLng()]);
                        }

                        // Auto-pan if out of view
                        if (!map.getBounds().contains(newPos)) {
                            map.panTo(newPos);
                        }
                    }

                    // Throttle backend updates
                    if (now - lastUpdateTime > UPDATE_INTERVAL) {
                        lastUpdateTime = now;
                        updateLocationToBackend(lat, lng, csrfToken);
                    }

                    // Update GPS status to active
                    updateGPSStatus('📡 GPS Aktif', '#22c55e');
                },
                function(error) {
                    console.error('GPS Error:', error);
                    handleGPSError(error);
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 15000
                }
            );
        }

        /**
         * Send location update to backend and check distance
         */
        function updateLocationToBackend(lat, lng, csrfToken) {
            fetch("{{ route('customer.tracking.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        latitude: lat,
                        longitude: lng
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.distance !== undefined) {
                        updateDistanceDisplay(data.distance, data.can_arrive, data.threshold);
                    }
                })
                .catch(error => {
                    console.error('Failed to update location:', error);
                });
        }

        /**
         * Update distance display and button visibility
         */
        function updateDistanceDisplay(distance, canArrive, threshold) {
            const distanceText = document.getElementById('distanceText');
            const buttonContainer = document.getElementById('arrivalButtonContainer');

            if (!distanceText) return;

            // Format distance
            let distanceStr = '';
            if (distance < 1000) {
                distanceStr = Math.round(distance) + ' meter dari toko';
            } else {
                distanceStr = (distance / 1000).toFixed(2) + ' km dari toko';
            }

            // Update text with color coding
            if (canArrive) {
                distanceText.innerHTML = `<span style="color:#22c55e; font-weight:bold;">${distanceStr} ✓</span>`;
            } else {
                distanceText.textContent = distanceStr;
            }

            // Show/hide button based on distance
            if (buttonContainer) {
                if (canArrive) {
                    buttonContainer.style.display = 'block';
                    // Add subtle animation when button appears
                    buttonContainer.style.animation = 'fadeInScale 0.3s ease-out';
                } else {
                    buttonContainer.style.display = 'none';
                }
            }
        }

        /**
         * Update GPS status indicator
         */
        function updateGPSStatus(text, color) {
            const gpsStatus = document.getElementById('gpsStatus');
            if (gpsStatus) {
                gpsStatus.querySelector('span').textContent = text;
                gpsStatus.querySelector('span').style.color = color;
                gpsStatus.querySelector('i').style.color = color;
                gpsStatus.style.background = `${color}15`; // 15 = hex for 15% opacity
            }
        }

        /**
         * Handle GPS errors
         */
        function handleGPSError(error) {
            let message = '';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = '❌ Izin lokasi ditolak. Aktifkan GPS di pengaturan.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = '⚠️ Lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    message = '⏱️ GPS timeout. Mencoba lagi...';
                    break;
                default:
                    message = '❌ GPS error.';
            }
            updateGPSStatus(message, '#ef4444');
        }

        /**
         * Show persistent GPS error
         */
        function showGPSError(message) {
            const gpsStatus = document.getElementById('gpsStatus');
            if (gpsStatus) {
                gpsStatus.innerHTML = `<i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><span style="color:#ef4444; font-weight:600;">${message}</span>`;
            }
        }

        function addTrackingBadge(text) {
            const Badge = L.Control.extend({
                onAdd: () => {
                    const div = L.DomUtil.create('div', 'tracking-control');
                    div.innerHTML = text;
                    return div;
                }
            });
            map.addControl(new Badge({
                position: 'topright'
            }));
        }

        function startPolling() {
            if (pollTimer) return;
            console.log("Starting tracking poll...");
            pollTimer = setInterval(() => {
                fetch(`/customer/order/${orderId}/tracking-status`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' && data.is_tracking_active) {
                            const newPos = L.latLng(data.last_lat, data.last_lng);
                            movingMarker.setLatLng(newPos);
                            if (routingControl) {
                                routingControl.setWaypoints([newPos, staticMarker.getLatLng()]);
                            }

                            // Auto pan if marker out of view
                            if (!map.getBounds().contains(newPos)) {
                                map.panTo(newPos);
                            }

                            // If arrived, we might want to reload or update status
                            if (data.shipment_status === 'arrived') {
                                // reload to show the "Arrived" status card if needed
                                // but we can also just update text
                                console.log("Status update: Arrived!");
                            }
                        } else if (!data.is_tracking_active) {
                            clearInterval(pollTimer);
                            console.log("Tracking stopped.");
                        }
                    })
                    .catch(e => console.error("Poll failed", e));
            }, 5000);
        }

        initMap();
    });
</script>
@endif


@endsection