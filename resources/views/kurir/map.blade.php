@extends('kurir.layouts.master')

@section('title', 'Peta Pengiriman')
@section('navbar')
@include('kurir.layouts.navbar')
@endsection
@section('navbot')
@include('kurir.layouts.navbot')
@endsection
@section('content')
<div class="courier-map-page">

    <!-- Hidden data for JS -->
    <input type="hidden" id="order_id_data" value="{{ $order->id }}">
    <input type="hidden" id="csrf_token_data" value="{{ csrf_token() }}">
    <input type="hidden" id="update_url_data" value="{{ route('kurir.update-location') }}">
    <input type="hidden" id="is_tracking_active_data" value="{{ ($mapData['shipment']['is_tracking_active'] ?? false) ? '1' : '0' }}">
    <input type="hidden" id="shipment_status_data" value="{{ $mapData['shipment']['status'] ?? '' }}">

    <!-- Header Info Card -->
    <div class="map-info-card">
        <div class="map-info-top">
            <h6 class="map-order-code">#{{ $order->order_code }}</h6>
            <span id="statusBadge" class="badge" style="
                @if($order->status === 'confirmed') background: #dbeafe; color: #1e40af;
                @elseif(($mapData['shipment']['status'] ?? '') === 'on_the_way') background: #fef3c7; color: #92400e;
                @elseif(($mapData['shipment']['status'] ?? '') === 'arrived') background: #d1fae5; color: #065f46;
                @elseif($order->status === 'ongoing') background: #ecfdf5; color: #047857;
                @else background: #f3f4f6; color: #374151;
                @endif
                padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                @if($order->status === 'confirmed') Perlu Diambil
                @elseif(($mapData['shipment']['status'] ?? '') === 'on_the_way') Sedang Dikirim
                @elseif(($mapData['shipment']['status'] ?? '') === 'arrived') Sudah Sampai
                @elseif($order->status === 'ongoing') Penyewaan Aktif
                @else {{ ucfirst($order->status) }}
                @endif</span>
        </div>

        <div class="map-info-row">
            <i class="fa fa-user" style="color: #22c55e; width: 20px;"></i>
            <span class="map-customer-name">{{ $mapData['customer']['name'] }}</span>
        </div>

        <div class="map-info-row align-start">
            <i class="fa fa-location-dot" style="color: #ef4444; width: 20px; margin-top: 2px;"></i>
            <span class="map-customer-address">{{ $mapData['customer']['address'] }}</span>
        </div>
    </div>

    <!-- Map Container -->
    <div id="deliveryMap" class="delivery-map"></div>

    <!-- Bottom Action Button -->
    <div class="map-bottom-action">
        @php
        $shipmentStatus = $mapData['shipment']['status'] ?? '';
        $shipmentId = $mapData['shipment']['id'] ?? '';
        @endphp

        <div id="courierActionsContainer">
            @if($shipmentStatus === 'assigned')
            {{-- DELIVERY: Button to Scan at Shop --}}
            <button id="btnScanPickup" onclick="window.location.href='{{ route('kurir.pickup.scan', $order->id) }}'"
                style="width: 100%; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                <i class="fa fa-qrcode"></i> Scan QR Ambil Barang
            </button>

            @elseif($shipmentStatus === 'picked_up')
            {{-- Ready to Transport --}}
            <button id="btnMainAction" onclick="handleStartTrip({{ $order->id }})"
                style="width: 100%; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">
                <i class="fa fa-truck-fast"></i> Mulai Perjalanan
            </button>

            @elseif($shipmentStatus === 'on_the_way')
            <div id="onTheWayContainer" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Distance Status -->
                <div id="distancePlaceholder" style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #3b82f6;">
                        <i class="fa fa-truck-fast" style="font-size: 20px;"></i>
                    </div>
                    <p style="margin: 0; font-size: 14px; color: #1f2937; font-weight: 600;">
                        Sedang Menuju Lokasi...
                    </p>
                    <p id="currentDistanceDisplay" style="margin: 5px 0; font-size: 16px; color: #3b82f6; font-weight: 800; display: none;">
                        <i class="fa fa-location-arrow"></i> <span id="currentDistanceText">0</span> m lagi
                    </p>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #6b7280;">Sistem sedang memantau lokasi Anda</p>
                </div>
            </div>

            @elseif($shipmentStatus === 'arrived')
            {{-- Arrived at Customer -> Photo Proof --}}
            @php
            $photoUrl = $shipmentId ? route('kurir.delivery-photo.show', $shipmentId) : '#';
            @endphp
            <button id="btnMainAction" onclick="window.location.href='{{ $photoUrl }}'"
                style="width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 20px; border-radius: 12px; font-size: 18px; font-weight: 800; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                <i class="fa fa-camera"></i> AMBIL FOTO BUKTI
            </button>

            @elseif($shipmentStatus === 'handed_over' || $order->status === 'ongoing')
            <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; text-align: center;">
                <div style="width: 50px; height: 50px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #059669;">
                    <i class="fa fa-qrcode" style="font-size: 24px;"></i>
                </div>
                <h6 style="margin: 0 0 4px 0; font-weight: 700; color: #111827; font-size: 16px;">Barang Diserahkan</h6>
                <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.4;">
                    Lanjutkan scan Foto untuk memulai masa sewa.
                </p>
                <button onclick="window.location.href='{{ $shipmentId ? route('kurir.delivery-photo.show', $shipmentId) : route('kurir.orders') }}'" style="margin-top: 15px; width: 100%; background: #10b981; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa fa-expand"></i> Buka Scanner
                </button>
            </div>
            @else
            <!-- Fallback for debugging -->
            <div style="background: #f3f4f6; color: #6b7280; padding: 10px; border-radius: 8px; text-align: center; font-size:12px;">
                Status: {{ $shipmentStatus }}
            </div>
            @endif
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
        .mobile-content {
            overflow: hidden !important;
        }

        .courier-map-page {
            position: relative;
            height: 100%;
            min-height: 100%;
            padding: 0;
            overflow: hidden;
        }

        .delivery-map {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .map-info-card {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .map-info-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .map-order-code {
            margin: 0;
            font-weight: 700;
            color: #1f2937;
            font-size: 14px;
        }

        .map-info-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 6px;
        }

        .map-info-row.align-start {
            align-items: flex-start;
            margin-bottom: 0;
        }

        .map-customer-name {
            font-size: 13px;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .map-customer-address {
            font-size: 12px;
            color: #9ca3af;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .map-bottom-action {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 82px;
            z-index: 1000;
        }

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

        .mobile-bottom-nav {
            position: relative;
            z-index: 1100;
        }

        @media (max-width: 360px), (max-height: 560px) {
            .map-info-card {
                top: 8px;
                left: 8px;
                right: 8px;
                padding: 10px;
            }

            .map-order-code {
                font-size: 12px;
            }

            .map-customer-name {
                font-size: 12px;
            }

            .map-customer-address {
                font-size: 11px;
            }

            .map-bottom-action {
                left: 10px;
                right: 10px;
                bottom: 74px;
            }

            .map-bottom-action button {
                padding: 12px !important;
                font-size: 14px !important;
            }
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