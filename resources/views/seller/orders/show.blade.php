@extends('frontend.masterseller')

@section('content')
<style>
    .order-detail-container {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: #3b82f6;
        border: 1px solid #3b82f6;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s;
    }

    .back-btn:hover {
        background: #eff6ff;
    }

    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .order-code-large {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-paid {
        background: #dbeafe;
        color: #1e40af;
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

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-penalty {
        background: #fee2e2;
        color: #b91c1b;
    }

    .status-assigned {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-picked_up {
        background: #d1fae5;
        color: #065f46;
    }

    .status-on_the_way {
        background: #d1fae5;
        color: #065f46;
    }

    .status-arrived {
        background: #a7f3d0;
        color: #065f46;
    }

    .status-delivered {
        background: #d1fae5;
        color: #065f46;
    }

    .status-returned {
        background: #fef3c7;
        color: #92400e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Delivery Badge Styles */
    .delivery-badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
    }

    .delivery-delivery {
        background: #fef3c7;
        color: #92400e;
    }

    .delivery-pickup {
        background: #e0e7ff;
        color: #3730a3;
    }

    .product-section {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .product-image {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        object-fit: cover;
        background: white;
        flex-shrink: 0;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 12px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #6b7280;
        font-size: 14px;
    }

    .info-row i {
        width: 20px;
        color: #9ca3af;
    }

    .info-label {
        font-weight: 500;
        color: #6b7280;
        min-width: 120px;
    }

    .info-value {
        color: #1a1a1a;
        font-weight: 500;
    }

    .customer-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .customer-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .customer-info {
        flex: 1;
    }

    .customer-info h4 {
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 8px 0;
    }

    .customer-info p {
        color: #6b7280;
        margin: 0 0 4px 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .customer-info p i {
        width: 16px;
    }

    .payment-info {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
    }

    .payment-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .payment-row.total {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        padding-top: 12px;
        border-top: 2px solid #e5e7eb;
        margin-top: 12px;
    }

    .payment-label {
        color: #6b7280;
    }

    .payment-value {
        font-weight: 600;
        color: #1a1a1a;
    }

    .payment-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }

    .payment-unpaid {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn {
        flex: 1;
        /* min-width: 150px; */
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-icon {
        font-size: 20px;
        margin-top: 2px;
    }

    .modal-dialog {
        background: white;
        border-radius: 12px;
        padding: 0;
        max-width: 500px;
        width: 90%;
        margin: 20px auto;
    }

    .modal-content {
        padding: 24px;
    }

    .modal-header {
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-body {
        margin-bottom: 24px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 16px;
        border-top: 2px solid #f3f4f6;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .text-error {
        color: #ef4444;
        font-size: 13px;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .shipment-timeline {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f3f4f6;
    }

    .timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-title {
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .timeline-time {
        font-size: 13px;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .order-detail-container {
            padding: 16px;
        }

        .create-header-bar {
            padding: 12px 16px;
            margin: -16px -16px 16px -16px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .back-btn {
            width: 100%;
            justify-content: center;
        }

        .product-section {
            flex-direction: column;
        }

        .product-image {
            width: 100%;
            height: 200px;
        }

        .order-status-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-badges {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<!-- Header -->
<div class="create-header-bar">
    <div class="create-header-back">
        <a href="{{ route('seller.orders') }}">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
    <div class="create-header-title">
        Detail Pesanan
    </div>
    <div class="create-header-spacer"></div>
</div>


<div class="order-detail-container">
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle alert-icon"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-times-circle alert-icon"></i>
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div>
            <strong>Terjadi kesalahan:</strong>
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Order Status -->
    <div class="detail-card">
        <div class="order-status-header">
            <div>
                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">KODE PESANAN</div>
                <div class="order-code-large">#{{ $order->order_code }}</div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @if($order->orderReturn && $order->orderReturn->payment_status === 'unpaid')
                <span class="status-badge status-penalty">
                    <i class="fas fa-triangle-exclamation"></i> Denda Belum Dibayar
                </span>
                @else
                <span class="status-badge status-{{ $order->status }}">
                    @if($order->status == 'pending')
                    <i class="fas fa-clock"></i> Menunggu
                    @elseif($order->status == 'confirmed')
                    <i class="fas fa-check-circle"></i> Dikonfirmasi
                    @elseif($order->status == 'ongoing')
                    <i class="fas fa-spinner"></i> Berlangsung
                    @elseif($order->status == 'completed')
                    <i class="fas fa-check-double"></i> Selesai
                    @elseif($order->status == 'cancelled')
                    <i class="fas fa-times-circle"></i> Dibatalkan
                    @else
                    {{ ucfirst($order->status) }}
                    @endif
                </span>

                <!-- Delivery Method Badge -->
                <span class="delivery-badge delivery-{{ $order->delivery_method }}">
                    @if($order->delivery_method == 'delivery')
                    <i class="fas fa-truck"></i> Antar
                    @else
                    <i class="fas fa-store"></i> Ambil Sendiri
                    @endif
                </span>

                @if($order->payment?->payment_status == 'paid')
                <div class="payment-badge payment-paid">
                    <i class="fas fa-check-circle"></i> Sudah Dibayar
                </div>
                @else
                <div class="payment-badge payment-unpaid">
                    <i class="fas fa-times-circle"></i> Belum Dibayar
                </div>
                @endif
                @endif
            </div>
        </div>

        <div class="info-row">
            <i class="fas fa-calendar"></i>
            <span class="info-label">Tanggal Pesanan:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order->created_at)->locale('id')->translatedFormat('d M Y, H:i') }}</span>
        </div>

        @if($order->paid_at)
        <div class="info-row">
            <i class="fas fa-money-bill-wave"></i>
            <span class="info-label">Tanggal Pembayaran:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order->paid_at)->locale('id')->translatedFormat('d M Y, H:i') }}</span>
        </div>
        @endif
    </div>

    <!-- Courier Assignment Section -->
    @if($order->delivery_method === 'delivery' && $order->status === 'confirmed')
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-truck"></i> Penugasan Kurir
        </h3>

        @php
        $shipment = $order->deliveryShipment;
        $isFormDisabled = false;
        $alertMessage = '';
        $alertType = '';

        if ($shipment) {
        $courierName = $shipment->courier ? $shipment->courier->user->name : 'Tidak Tersedia';

        if ($shipment->status === 'pending') {
        $isFormDisabled = true;
        $alertMessage = "Menunggu kurir <strong>{$courierName}</strong> untuk menerima atau menolak pesanan.";
        $alertType = 'info';
        } elseif ($shipment->status === 'rejected') {
        $isFormDisabled = false;
        $rejectedBy = $shipment->rejected_by ?? [];
        $rejectedNames = [];
        foreach ($rejectedBy as $courierId) {
        $courier = \App\Models\Courier::with('user')->find($courierId);
        if ($courier) {
        $rejectedNames[] = $courier->user->name;
        }
        }
        $rejectedList = implode(', ', $rejectedNames);
        $alertMessage = "Kurir <strong>{$rejectedList}</strong> menolak pesanan ini.";
        if ($shipment->rejection_reason) {
        $alertMessage .= " Alasan: <em>{$shipment->rejection_reason}</em>";
        }
        $alertType = 'danger';
        } elseif (in_array($shipment->status, ['assigned', 'picked_up', 'on_the_way', 'arrived', 'delivered'])) {
        $isFormDisabled = true;
        $statusText = [
        'assigned' => 'sedang mempersiapkan',
        'picked_up' => 'telah mengambil',
        'on_the_way' => 'sedang dalam perjalanan',
        'arrived' => 'telah tiba',
        'delivered' => 'telah mengirimkan'
        ][$shipment->status] ?? 'sedang memproses';
        $alertMessage = "Kurir <strong>{$courierName}</strong> {$statusText} pesanan ini.";
        $alertType = 'success';
        }
        }
        @endphp

        <!-- Current Shipment Status -->
        @if($shipment)
        <div class="info-row">
            <i class="fas fa-info-circle"></i>
            <span class="info-label">Status Pengiriman:</span>
            <span class="info-value">
                <span class="status-badge status-{{ $shipment->status }}">
                    @if($shipment->status == 'pending')
                    Menunggu
                    @elseif($shipment->status == 'assigned')
                    Ditugaskan
                    @elseif($shipment->status == 'picked_up')
                    Diambil
                    @elseif($shipment->status == 'on_the_way')
                    Dalam Perjalanan
                    @elseif($shipment->status == 'arrived')
                    Tiba
                    @elseif($shipment->status == 'delivered')
                    Terkirim
                    @elseif($shipment->status == 'rejected')
                    Ditolak
                    @else
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                    @endif
                </span>
            </span>
        </div>

        @if($shipment->courier)
        <div class="info-row">
            <i class="fas fa-user"></i>
            <span class="info-label">Kurir yang Ditugaskan:</span>
            <span class="info-value">{{ $shipment->courier->user->name }} ({{ $shipment->courier->user->phone }})</span>
        </div>
        @endif

        @if($shipment->assigned_at)
        <div class="info-row">
            <i class="fas fa-calendar-check"></i>
            <span class="info-label">Ditugaskan Pada:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($shipment->assigned_at)->locale('id')->translatedFormat('d M Y, H:i') }}</span>
        </div>
        @endif

        @if($alertMessage)
        <div class="alert alert-{{ $alertType }}" style="margin-top: 15px;">
            <i class="fas fa-{{ $alertType === 'danger' ? 'times-circle' : ($alertType === 'info' ? 'hourglass-half' : 'check-circle') }} alert-icon"></i>
            <div>
                {!! $alertMessage !!}
            </div>
        </div>
        @endif

        <!-- Shipment Timeline -->
        <div class="shipment-timeline">
            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #374151;">Riwayat Pengiriman</h4>

            @if($shipment->created_at)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Pengiriman Dibuat</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->created_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @endif

            @if($shipment->assigned_at && $shipment->courier)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Ditugaskan ke Kurir</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->assigned_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                    <div style="font-size: 13px; color: #6b7280;">Kurir: {{ $shipment->courier->user->name }}</div>
                </div>
            </div>
            @endif

            @if($shipment->picked_up_at)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Barang Diambil</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->picked_up_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @endif

            @if($shipment->delivered_at)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Barang Dikirim</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->delivered_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Courier Assignment Form -->
        @if(!$isFormDisabled)
        <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid #f3f4f6;">
            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #374151;">
                <i class="fas fa-user-plus"></i>
                {{ $shipment ? 'Tugaskan Ulang Kurir' : 'Tugaskan Kurir' }}
            </h4>

            <form action="{{ $shipment ? route('seller.courier-assignments.reassign-courier', $order->id) : route('seller.courier-assignments.assign-courier', $order->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Pilih Kurir
                    </label>
                    <select name="courier_id" class="form-control" required>
                        <option value="">-- Pilih Kurir --</option>
                        @foreach($availableCouriers as $courier)
                        <option value="{{ $courier->id }}">
                            {{ $courier->user->name }} - {{ $courier->user->phone }}
                        </option>
                        @endforeach
                    </select>
                    @error('courier_id')
                    <p class="text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                    @if($availableCouriers->count() == 0)
                    <p class="text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        Tidak ada kurir yang tersedia. Semua kurir aktif telah menolak pesanan ini.
                    </p>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-sticky-note"></i> Catatan Pengiriman (Opsional)
                    </label>
                    <textarea name="courier_notes" class="form-control" rows="4" placeholder="Instruksi khusus untuk kurir...">{{ old('courier_notes', $shipment->courier_notes ?? '') }}</textarea>
                    @error('courier_notes')
                    <p class="text-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" {{ $availableCouriers->count() == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> {{ $shipment ? 'Tugaskan Ulang Kurir' : 'Tugaskan Kurir' }}
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @elseif($order->delivery_method === 'pickup' && in_array($order->status, ['confirmed', 'ongoing']))
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-store"></i> Status Pengambilan (Pickup)
        </h3>

        @php
        $shipment = $order->deliveryShipment;
        @endphp

        @if($shipment)
        <div class="info-row">
            <i class="fas fa-info-circle"></i>
            <span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ $shipment->status }}">
                    @if($shipment->status == 'on_the_way')
                    Dalam Perjalanan
                    @elseif($shipment->status == 'arrived')
                    Sudah Sampai
                    @else
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                    @endif
                </span>
            </span>
        </div>

        <div class="alert alert-{{ $shipment->status === 'arrived' ? 'success' : 'info' }}" style="margin-top: 15px;">
            <i class="fas fa-{{ $shipment->status === 'arrived' ? 'check-circle' : 'person-walking-luggage' }} alert-icon"></i>
            <div>
                @if($shipment->status == 'on_the_way')
                <strong>Pelanggan sedang dalam perjalanan</strong> menuju toko Anda.
                @elseif($shipment->status == 'arrived')
                <strong>Pelanggan sudah sampai!</strong> Silakan siapkan barang untuk diserahkan.
                @else
                Menunggu update dari pelanggan.
                @endif
            </div>
        </div>

        <!-- Pickup Timeline -->
        <div class="shipment-timeline">
            @if($shipment->picked_up_at)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-play" style="font-size: 12px;"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Perjalanan Dimulai</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->picked_up_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @endif

            @if($shipment->status == 'arrived')
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-location-dot"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Tiba di Toko</div>
                    <div class="timeline-time">{{ \Carbon\Carbon::parse($shipment->updated_at)->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="alert alert-warning" style="margin-top: 15px;">
            <i class="fas fa-hourglass-half alert-icon"></i>
            <div>
                <strong>Menunggu pelanggan</strong> untuk memulai perjalanan pengambilan barang (Pickup).
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Product Information -->
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-box"></i> Informasi Produk
        </h3>

        <div class="product-section">
            <div class="product-image">
                @if($order->productRental->product->images->first())
                <img src="{{ asset($order->productRental->product->images->first()->image_path) }}"
                    alt="{{ $order->productRental->product->name }}">
                @else
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 48px;">
                    📷
                </div>
                @endif
            </div>

            <div class="product-info">
                <div class="product-name">{{ $order->productRental->product->name }}</div>

                <div class="info-row">
                    <i class="fas fa-tag"></i>
                    <span>{{ $order->productRental->product->category->name ?? 'Tidak Tersedia' }}</span>
                </div>

                <div class="info-row">
                    <i class="fas fa-clock"></i>
                    <span>Durasi: {{ $order->productRental->duration }} {{ $order->productRental->duration_unit }}</span>
                </div>

                <div class="info-row">
                    <i class="fas fa-money-bill"></i>
                    <span>Harga: Rp {{ number_format($order->productRental->price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-user"></i> Informasi Pelanggan
        </h3>

        <div class="customer-card">
            <div class="customer-avatar">
                {{ strtoupper(substr($order->user->name, 0, 1)) }}
            </div>
            <div class="customer-info">
                <h4>{{ $order->user->name }}</h4>
                <p><i class="fas fa-envelope"></i> {{ $order->user->email }}</p>
                <p><i class="fas fa-phone"></i> {{ $order->user->phone }}</p>
            </div>
        </div>
    </div>




    {{-- Return Shipment Management Removed --}}

    <!-- Payment Summary -->
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-receipt"></i> Ringkasan Pembayaran
        </h3>

        <div class="payment-info">
            <div class="payment-row">
                <span class="payment-label">Harga Sewa</span>
                <span class="payment-value">Rp {{ number_format($order->productRental->price, 0, ',', '.') }}</span>
            </div>

            <div class="payment-row">
                <span class="payment-label">Durasi</span>
                <span class="payment-value">{{ $order->productRental->duration }} {{ $order->productRental->duration_unit }}</span>
            </div>

            <div class="payment-row total">
                <span>Total Jumlah</span>
                <span>Rp {{ number_format($order->payment?->total_amount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Penalty Information -->
    @if($order->orderReturn)
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-triangle-exclamation"></i> Informasi Denda
        </h3>

        <div class="info-row">
            <i class="fas fa-money-bill-wave"></i>
            <span class="info-label">Jumlah Denda:</span>
            <span class="info-value" style="color:#dc3545">
                Rp {{ number_format($order->orderReturn->penalties_amount, 0, ',', '.') }}
            </span>
        </div>

        <div class="info-row">
            <i class="fas fa-clock"></i>
            <span class="info-label">Status Pembayaran:</span>
            <span class="info-value">
                @if($order->orderReturn->payment_status === 'paid')
                <i class="fas fa-check-circle"></i> Sudah Dibayar
                @else
                <i class="fas fa-clock"></i> Belum Dibayar
                @endif
            </span>
        </div>

        @if($order->orderReturn->returned_at)
        <div class="info-row">
            <i class="fas fa-calendar-check"></i>
            <span class="info-label">Dikembalikan Pada:</span>
            <span class="info-value">
                {{ \Carbon\Carbon::parse($order->orderReturn->returned_at)->locale('id')->translatedFormat('l, d F Y - H:i') }}
            </span>
        </div>
        @endif
    </div>
    @endif

    {{-- BUKTI HANDOVER KURIR (DELIVERY) --}}
    @if($order->deliveryShipment && $order->deliveryShipment->delivery_proof_photo)
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-truck"></i> Foto Bukti Serah Barang Kurir (Antar)
        </h3>

        {{-- Nama Kurir --}}
        @if($order->deliveryShipment->courier)
        <div class="info-row">
            <i class="fas fa-user"></i>
            <span class="info-label">Kurir:</span>
            <span class="info-value">
                {{ $order->deliveryShipment->courier->user->name }}
            </span>
        </div>
        @endif

        {{-- Waktu Terima --}}
        @if($order->deliveryShipment->delivery_proof_photo_at)
        <div class="info-row">
            <i class="fas fa-calendar-check"></i>
            <span class="info-label">Diterima:</span>
            <span class="info-value">
                {{ \Carbon\Carbon::parse($order->deliveryShipment->delivery_proof_photo_at)->locale('id')->translatedFormat('d M Y, H:i') }}
            </span>
        </div>
        @endif

        {{-- Foto --}}
        <div style="margin-top:16px;">
            <div style="
                border:1px solid #e5e7eb;
                border-radius:12px;
                overflow:hidden;
                max-width:320px;
            ">
                <a href="{{ asset($order->deliveryShipment->delivery_proof_photo) }}" target="_blank">
                    <img
                        src="{{ asset($order->deliveryShipment->delivery_proof_photo) }}"
                        alt="Bukti Handover Delivery"
                        style="width:100%; display:block;">
                </a>
            </div>

            <p style="margin-top:8px; font-size:13px; color:#6b7280;">
                Klik foto untuk melihat ukuran penuh
            </p>
        </div>
    </div>
    @endif

    {{-- FOTO BUKTI SERAH BARANG --}}
    @if($order->handoverProof)
    <div class="detail-card">
        <h3 class="card-title">
            <i class="fas fa-camera"></i> Foto Bukti Serah Barang
        </h3>

        <div class="info-row">
            <i class="fas fa-calendar-check"></i>
            <span class="info-label">Diunggah:</span>
            <span class="info-value">
                {{ \Carbon\Carbon::parse($order->handoverProof->created_at)->locale('id')->translatedFormat('d M Y, H:i') }}
            </span>
        </div>

        <div style="margin-top:16px;">
            <div style="
                border:1px solid #e5e7eb;
                border-radius:12px;
                overflow:hidden;
                max-width:320px;
            ">
                <a href="{{ asset($order->handoverProof->photo_path) }}" target="_blank">
                    <img
                        src="{{ asset($order->handoverProof->photo_path) }}"
                        alt="Foto Bukti Serah Barang"
                        style="width:100%; display:block;">
                </a>
            </div>

            <p style="margin-top:8px; font-size:13px; color:#6b7280;">
                Klik foto untuk melihat ukuran penuh
            </p>
        </div>
    </div>
    @endif
    @if(in_array($order->status, ['confirmed', 'ongoing']) && $order->payment?->payment_status === 'paid')
    <!-- QR Code Section -->
    <div class="detail-card">
        <h3 class="card-title" style="margin-bottom: 10px;">
            <i class="fas fa-qrcode"></i> QR Code Pesanan
        </h3>
        <div style="text-align:center; margin-top:10px">
            <div style="display: inline-block; background: white; padding: 8px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                @if($order->qr_code && file_exists(storage_path('app/public/' . $order->qr_code)))
                    <img src="{{ asset($order->qr_code) }}"
                        alt="QR Code Pesanan"
                        style="width: 180px; height: 180px; display: block;">
                @else
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->generate($order->order_code) !!}
                @endif
            </div>
            <p style="font-size:14px; font-weight:700; margin-top:12px; margin-bottom: 2px; color:#333;">
                #{{ $order->order_code }}
            </p>
            <p style="font-size:12px; margin-top:0; color:#666;">
                Pelanggan: {{ $order->user->name }}
            </p>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal handling for reassigning
        const reassignButtons = document.querySelectorAll('[data-modal-target]');
        reassignButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal
        const closeButtons = document.querySelectorAll('.modal-close, .modal-cancel');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal-dialog');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal-dialog');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    });
</script>
@endsection