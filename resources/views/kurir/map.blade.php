@extends('kurir.layouts.master')

@section('title', 'Peta Pengiriman')
@section('navbar')
@include('kurir.layouts.navbar')
@endsection
@section('navbot')
@include('kurir.layouts.navbot')
@endsection
@section('content')
<div style="padding: 0; height: 100vh; position: relative; padding-bottom: 85px;">

    <!-- Hidden data for JS -->
    <input type="hidden" id="order_id_data" value="{{ $order->id }}">
    <input type="hidden" id="csrf_token_data" value="{{ csrf_token() }}">
    <input type="hidden" id="update_url_data" value="{{ route('kurir.update-location') }}">
    <input type="hidden" id="is_tracking_active_data" value="{{ ($mapData['shipment']['is_tracking_active'] ?? false) ? '1' : '0' }}">
    <input type="hidden" id="shipment_status_data" value="{{ $mapData['shipment']['status'] ?? '' }}">

    <!-- Header Info Card -->
    <div style="position: absolute; top: 15px; left: 15px; right: 15px; z-index: 1000; background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h6 style="margin: 0; font-weight: 700; color: #1f2937;">#{{ $order->order_code }}</h6>
            <span id="statusBadge" class="badge" style="
                @if($order->status === 'confirmed') background: #dbeafe; color: #1e40af;
                @elseif(($mapData['shipment']['status'] ?? '') === 'on_the_way') background: #fef3c7; color: #92400e;
                @elseif(($mapData['shipment']['status'] ?? '') === 'arrived') background: #d1fae5; color: #065f46;
                @elseif($order->status === 'ongoing') background: #ecfdf5; color: #047857;
                @elseif($order->status === 'awaiting_return') background: #fee2e2; color: #991b1b;
                @else background: #f3f4f6; color: #374151;
                @endif
                padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                @if($order->status === 'confirmed') Perlu Diambil
                @elseif(($mapData['shipment']['status'] ?? '') === 'on_the_way') Sedang Dikirim
                @elseif(($mapData['shipment']['status'] ?? '') === 'arrived') Sudah Sampai
                @elseif($order->status === 'ongoing') Penyewaan Aktif
                @elseif($order->status === 'awaiting_return') Menunggu Pengembalian
                @else {{ ucfirst($order->status) }}
                @endif
            </span>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
            <i class="fa fa-user" style="color: #22c55e; width: 20px;"></i>
            <span style="font-size: 13px; color: #6b7280;">{{ $mapData['customer']['name'] }}</span>
        </div>

        <div style="display: flex; gap: 10px; align-items: flex-start;">
            <i class="fa fa-location-dot" style="color: #ef4444; width: 20px; margin-top: 2px;"></i>
            <span style="font-size: 12px; color: #9ca3af; flex: 1;">{{ $mapData['customer']['address'] }}</span>
        </div>
    </div>

    <!-- Map Container -->
    <div id="deliveryMap" style="height: 100vh; width: 100%;"></div>

    <!-- Bottom Action Button -->
    <div style="position: absolute; bottom: 150px; left: 15px; right: 15px; z-index: 1000;">
        @php
        $shipmentStatus = $mapData['shipment']['status'] ?? '';
        @endphp

        <div id="actionButtonContainer">
            @php
            $shipmentStatus = $mapData['shipment']['status'] ?? '';
            $shipmentType = $mapData['shipment']['type'] ?? 'delivery'; // Default to delivery
            $isReturn = $shipmentType === 'return';
            $pickedUpAt = $mapData['shipment']['picked_up_at'] ?? null;
            $shipmentId = $mapData['shipment']['id'] ?? '';
            @endphp

            <div id="actionButtonContainer">
                @if($shipmentStatus === 'assigned')

                {{-- DELIVERY: Button to Scan at Shop --}}
                <button id="btnScanPickup" onclick="window.location.href='{{ route('kurir.pickup.scan', $order->id) }}'"
                    style="width: 100%; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                    <i class="fa fa-qrcode"></i> Scan QR Ambil Barang
                </button>


                @elseif($shipmentStatus === 'picked_up')
                {{-- Both Flows: Ready to Transport (Delivery: to Customer, Return: to Shop) --}}
                <button id="btnMainAction" onclick="handleStartTrip({{ $order->id }})"
                    style="width: 100%; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">
                    <i class="fa fa-truck-fast"></i> Mulai Perjalanan
                </button>

                @elseif($shipmentStatus === 'on_the_way')
                <div id="onTheWayContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Placeholder UI -->
                    <div id="distancePlaceholder" style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #3b82f6;">
                            <i class="fa fa-truck-fast" style="font-size: 20px;"></i>
                        </div>
                        <p style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                            @if($isReturn && !$pickedUpAt)
                            Menuju Lokasi Penjemputan...
                            @elseif($isReturn && $pickedUpAt)
                            Sedang Kembali ke Toko...
                            @else
                            Sedang Menuju Lokasi...
                            @endif
                        </p>
                        <p id="currentDistanceDisplay" style="margin: 5px 0; font-size: 16px; color: #3b82f6; font-weight: 800; display: none;">
                            <i class="fa fa-location-arrow"></i> <span id="currentDistanceText">0</span> m lagi
                        </p>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #6b7280;">Sistem sedang memantau lokasi Anda</p>
                    </div>

                    <!-- Manual Button (Always visible for testing/fallback) -->
                    <button id="btnManualArrived" type="button" onclick="handleConfirmArrived({{ $order->id }})"
                        style="width: 100%; background: white; color: #DC2626; border: 1px solid #DC2626; padding: 16px; border-radius: 12px; font-size: 14px; font-weight: 700;">
                        <i class="fa fa-location-crosshairs"></i> Konfirmasi Sampai Manual
                    </button>
                </div>

                @elseif($shipmentStatus === 'arrived')
                @if($isReturn && $pickedUpAt)
                {{-- RETURN (Leg 2): Arrived at Shop -> Handover --}}
                @php
                $photoUrl = route('kurir.delivery-photo.show', $shipmentId);
                @endphp
                <button onclick="window.location.href='{{ $photoUrl }}'"
                    style="width: 100%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; padding: 20px; border-radius: 12px; font-size: 18px; font-weight: 800; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
                    <i class="fa fa-camera"></i> Foto Toko (Selesai)
                </button>
                @else
                {{-- DELIVERY or RETURN (Leg 1): Arrived at Customer -> Photo Proof --}}
                @php
                $photoUrl = $shipmentId ? route('kurir.delivery-photo.show', $shipmentId) : '#';
                @endphp
                <button id="btnMainAction" onclick="window.location.href='{{ $photoUrl }}'"
                    style="width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 20px; border-radius: 12px; font-size: 18px; font-weight: 800; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                    <i class="fa fa-camera"></i> {{ $isReturn ? 'AMBIL FOTO BARANG' : 'AMBIL FOTO BUKTI' }}
                </button>
                @endif

                @elseif($shipmentStatus === 'handed_over' || $order->status === 'ongoing')
                <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; text-align: center;">
                    <div style="width: 50px; height: 50px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #059669;">
                        <i class="fa fa-qrcode" style="font-size: 24px;"></i>
                    </div>
                    <h6 style="margin: 0 0 4px 0; font-weight: 700; color: #111827; font-size: 16px;">Barang Diserahkan</h6>
                    <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.4;">
                        Lanjutkan scan Foto untuk memulai masa sewa.
                    </p>
                    <button onclick="window.location.href='{{ route('kurir.delivery-photo.index') }}'" style="margin-top: 15px; width: 100%; background: #10b981; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa fa-expand"></i> Buka Scanner
                    </button>
                </div>
                @else
                <!-- Fallback for debugging - Show status if no match -->
                <div style="background: red; color: white; padding: 10px; border-radius: 8px; text-align: center;">
                    Status Unknown: {{ $shipmentStatus }} <br>
                    Order Status: {{ $order->status }}
                </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <!-- Leaflet Routing Machine JS -->
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="https://unpkg.com/lrm-mapbox@1.2.0/dist/lrm-mapbox.min.js"></script>

    <style>
        /* Hide routing panel */
        .leaflet-routing-container {
            display: none !important;
        }

        /* Custom marker styles */
        .custom-icon {
            background: white;
            border-radius: 50%;
            padding: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .shop-icon {
            color: #3b82f6;
            border: 3px solid #3b82f6;
        }

        .customer-icon {
            color: #22c55e;
            border: 3px solid #22c55e;
        }
    </style>

    <script src="{{ asset('js/courier-tracking.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.CourierTracking) {
                window.CourierTracking.init({
                    orderId: document.getElementById('order_id_data').value,
                    csrfToken: document.getElementById('csrf_token_data').value,
                    updateUrl: document.getElementById('update_url_data').value,
                    isTrackingActive: document.getElementById('is_tracking_active_data').value,
                    shipmentStatus: document.getElementById('shipment_status_data').value,
                    mapboxToken: "{{ config('services.mapbox.token') }}",
                    mapData: @json($mapData)
                });
            }
        });
    </script>

    @endsection