@extends('admin.layouts.app')

@section('title', 'Peta Toko Seller')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />

    <style>
        #shop-map {
            width: 100%;
            height: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1;
            /* Ensure map stays below other fixed elements if any */
        }

        .map-container {
            position: relative;
            margin-bottom: 20px;
        }

        .map-stats {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            min-width: 150px;
        }

        .stat-item i {
            font-size: 20px;
        }

        .stat-value {
            font-weight: 700;
            font-size: 18px;
            color: #333;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
            display: block;
        }

        .map-legend {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #3b82f6;
        }

        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
        }

        .custom-popup .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .popup-header {
            padding: 12px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-body {
            padding: 15px;
        }

        .popup-footer {
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: right;
        }

        @media (max-width: 768px) {
            #shop-map {
                height: 400px;
            }

            .map-stats {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Peta Toko Seller</h4>
                <p class="text-muted mb-0">Lokasi persebaran toko seller di seluruh Indonesia</p>
            </div>
            <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        @if ($total > 0)
            <!-- Map Stats -->
            <div class="map-stats">
                <div class="stat-item">
                    <i class="bi bi-shop text-primary"></i>
                    <div class="d-flex flex-column">
                        <span class="stat-value">{{ $total }}</span>
                        <span class="stat-label">Total Toko</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div class="d-flex flex-column">
                        <span class="stat-value">{{ $shops->where('is_active', true)->count() }}</span>
                        <span class="stat-label">Toko Aktif</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="bi bi-x-circle-fill text-danger"></i>
                    <div class="d-flex flex-column">
                        <span class="stat-value">{{ $shops->where('is_active', false)->count() }}</span>
                        <span class="stat-label">Tidak Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <div id="shop-map"></div>
                </div>
            </div>

            <!-- Legend -->
            <div class="map-legend shadow-sm">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Keterangan Marker</h6>
                <div class="d-flex gap-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle border border-3 border-success me-2"
                            style="width: 25px; height: 25px; background: #eee;"></div>
                        <span>Toko Aktif (Border Hijau)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle border border-3 border-danger me-2"
                            style="width: 25px; height: 25px; background: #eee;"></div>
                        <span>Toko Tutup (Border Merah)</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-geo-alt"></i>
                <h4>Belum Ada Lokasi Toko</h4>
                <p class="text-muted">Saat ini belum ada toko yang mendaftarkan koordinat lokasi mereka.</p>
                <a href="{{ route('admin.shops.index') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-shop me-1"></i> Kelola Toko
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shops = @json($shops);
            const hasShops = shops.length > 0;

            if (hasShops) {
                // Initialize map
                // Default center Indonesia if no bounds set yet
                const map = L.map('shop-map').setView([-2.5489, 118.0149], 5);

                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                const markers = [];

                shops.forEach(shop => {
                    // Determine logo URL (use UI Avatars fallback if null)
                    const logoUrl = shop.logo_url ||
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(shop.name)}&background=random&color=fff&size=50`;

                    // Determine border color based on status
                    const borderColor = shop.is_active ? '#198754' :
                        '#dc3545'; // Bootstrap success or danger color

                    // Create custom icon using Shop Profile Photo
                    const customIcon = L.divIcon({
                        className: 'custom-shop-marker',
                        html: `
                        <div style="
                            background-image: url('${logoUrl}');
                            background-size: cover;
                            background-position: center;
                            width: 48px;
                            height: 48px;
                            border-radius: 50%;
                            border: 3px solid ${borderColor};
                            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                            background-color: white;
                            transition: transform 0.2s;
                        "></div>
                    `,
                        iconSize: [48, 48],
                        iconAnchor: [24, 24], // Center of the icon
                        popupAnchor: [0, -28] // Popup opens above the icon
                    });

                    const statusBadge = shop.is_active ?
                        '<span class="badge bg-success">Aktif</span>' :
                        '<span class="badge bg-danger">Tutup</span>';

                    const popupContent = `
                    <div class="custom-popup-content">
                        <div class="popup-header">
                            <span class="text-truncate" style="max-width: 180px;">${shop.name}</span>
                            ${statusBadge}
                        </div>
                        <div class="popup-body">
                            <div class="text-center mb-3">
                                <img src="${logoUrl}" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <p class="mb-2 text-muted small"><i class="bi bi-geo-alt me-1"></i> ${shop.address}</p>
                            <small class="text-muted d-block mb-2">Lat: ${shop.latitude}<br>Long: ${shop.longitude}</small>
                        </div>
                        <div class="popup-footer">
                            <a href="${shop.detail_url}" class="btn btn-sm btn-primary w-100">
                                Lihat Detail Toko <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                `;

                    const marker = L.marker([shop.latitude, shop.longitude], {
                            icon: customIcon
                        })
                        .bindPopup(popupContent, {
                            className: 'custom-popup'
                        })
                        .addTo(map);

                    // Add hover effect
                    marker.on('mouseover', function(e) {
                        this.setZIndexOffset(1000);
                        const el = e.target.getElement().querySelector('div');
                        if (el) el.style.transform = 'scale(1.15)';
                    });

                    marker.on('mouseout', function(e) {
                        this.setZIndexOffset(0);
                        const el = e.target.getElement().querySelector('div');
                        if (el) el.style.transform = 'scale(1)';
                    });

                    markers.push(marker);
                });
                // Auto fit bounds to show all markers
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds(), {
                        padding: [50, 50]
                    });
                }
            }
        });
    </script>
@endpush
