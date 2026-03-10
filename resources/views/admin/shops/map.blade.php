@extends('admin.layouts.app')

@section('title', 'Peta Toko Seller')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />

<style>
    /* Force Fullscreen Layout Overrides */
    .content-wrapper {
        padding: 0 !important;
        height: calc(100vh - 60px);
        /* 60px is topbar height */
        position: relative;
        overflow: hidden;
    }

    .container-fluid {
        height: 100%;
        padding: 0 !important;
    }

    .footer {
        display: none !important;
    }

    /* Map Container */
    #shop-map {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Floating Overlays */
    .map-overlay {
        position: absolute;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 15px;
        transition: all 0.3s ease;
    }

    /* Header Overlay (Top Left) */
    .overlay-header {
        top: 20px;
        left: 20px;
        width: 300px;
    }

    /* Stats Overlay (Top Right) */
    .overlay-stats {
        top: 20px;
        right: 20px;
        display: flex;
        gap: 15px;
        padding: 10px 15px;
    }

    /* Legend Overlay (Bottom Left) */
    .overlay-legend {
        bottom: 30px;
        left: 20px;
    }

    /* Stat Items in Overlay */
    .map-stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .map-stat-item i {
        font-size: 18px;
    }

    .map-stat-value {
        font-weight: 700;
        font-size: 16px;
    }

    .map-stat-label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .overlay-header {
            top: 10px;
            left: 10px;
            right: 10px;
            width: auto;
        }

        .overlay-stats {
            top: auto;
            bottom: 150px;
            /* Above legend/attribution */
            right: 10px;
            flex-direction: column;
            gap: 5px;
        }

        .overlay-legend {
            bottom: 25px;
            left: 10px;
            right: 10px;
        }
    }

    /* Popup Styles */
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
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
</style>
@endpush

@section('content')
<div class="container-fluid position-relative h-100 p-0">

    <!-- Fullscreen Map -->
    <div id="shop-map"></div>

    <!-- 1. Overlay Header (Top Left) -->
    <div class="map-overlay overlay-header">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h5 class="mb-1 fw-bold text-dark">Peta Toko</h5>
                <p class="text-muted small mb-0">Lokasi seller terdaftar</p>
            </div>
            <a href="{{ route('admin.shops.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle" data-bs-toggle="tooltip" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
        </div>
    </div>

    @if ($total > 0)
    <!-- 2. Overlay Stats (Top Right) -->
    <div class="map-overlay overlay-stats">
        <div class="map-stat-item" title="Total Toko">
            <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <div class="map-stat-value">{{ $total }}</div>
                <div class="map-stat-label">Total</div>
            </div>
        </div>
        <div class="vr mx-1"></div>
        <div class="map-stat-item" title="Toko Aktif">
            <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <div class="map-stat-value">{{ $shops->where('is_active', true)->count() }}</div>
                <div class="map-stat-label">Aktif</div>
            </div>
        </div>
        <div class="vr mx-1"></div>
        <div class="map-stat-item" title="Toko Non-Aktif">
            <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger">
                <i class="bi bi-x-lg"></i>
            </div>
            <div>
                <div class="map-stat-value">{{ $shops->where('is_active', false)->count() }}</div>
                <div class="map-stat-label">Tutup</div>
            </div>
        </div>
    </div>

    <!-- 3. Overlay Legend (Bottom Left) -->
    <div class="map-overlay overlay-legend">
        <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="bi bi-info-circle me-1"></i> Keterangan</h6>
        <div class="d-flex gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-block rounded-circle border border-3 border-success bg-white"
                    style="width: 14px; height: 14px;"></span>
                <span class="small fw-medium">Aktif</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-block rounded-circle border border-3 border-danger bg-white"
                    style="width: 14px; height: 14px;"></span>
                <span class="small fw-medium">Tutup</span>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State Overlay (Center) -->
    <div class="position-absolute top-50 start-50 translate-middle text-center bg-white p-4 rounded-4 shadow-lg" style="z-index: 2000; min-width: 300px;">
        <div class="mb-3 text-muted">
            <i class="bi bi-geo-alt display-1 opacity-25"></i>
        </div>
        <h5 class="fw-bold">Belum Ada Lokasi</h5>
        <p class="text-muted small mb-3">Tidak ada data koordinat toko</p>
        <a href="{{ route('admin.shops.index') }}" class="btn btn-primary btn-sm rounded-pill px-4">
            Kelola Toko
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
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        const shops = @json($shops);

        // Initialize map with Indonesia view
        const map = L.map('shop-map', {
            zoomControl: false // We will add it safely
        }).setView([-2.5489, 118.0149], 5);

        // Move zoom control to bottom right
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Add OpenStreetMap tile layer (Clean Style)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        const markers = [];

        if (shops.length > 0) {
            shops.forEach(shop => {
                // Determine logo URL
                const logoUrl = shop.logo_url ||
                    `https://ui-avatars.com/api/?name=${encodeURIComponent(shop.name)}&background=random&color=fff&size=50`;

                const borderColor = shop.is_active ? '#198754' : '#dc3545';

                // Create custom marker
                const customIcon = L.divIcon({
                    className: 'custom-shop-marker',
                    html: `
                        <div style="
                            background-image: url('${logoUrl}');
                            background-size: cover;
                            background-position: center;
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            border: 3px solid ${borderColor};
                            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                            background-color: white;
                            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        "></div>
                    `,
                    iconSize: [44, 44],
                    iconAnchor: [22, 22],
                    popupAnchor: [0, -24]
                });

                const statusBadge = shop.is_active ?
                    '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Aktif</span>' :
                    '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Tutup</span>';

                const popupContent = `
                    <div class="custom-popup-content">
                        <div class="popup-header">
                            <span class="text-truncate fw-bold" style="max-width: 180px;">${shop.name}</span>
                            ${statusBadge}
                        </div>
                        <div class="popup-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="${logoUrl}" class="rounded-3 border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="small text-muted" style="line-height: 1.4;">
                                    ${shop.address || 'Alamat tidak tersedia'}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted bg-light p-2 rounded">
                                <span>Lat: ${parseFloat(shop.latitude).toFixed(4)}</span>
                                <span>Lng: ${parseFloat(shop.longitude).toFixed(4)}</span>
                            </div>
                        </div>
                        <div class="popup-footer">
                            <a href="${shop.detail_url}" class="btn btn-sm btn-primary w-100 rounded-pill text-white">
                                Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                `;

                const marker = L.marker([shop.latitude, shop.longitude], {
                        icon: customIcon
                    })
                    .bindPopup(popupContent, {
                        className: 'custom-popup',
                        closeButton: false
                    })
                    .addTo(map);

                // Add hover animation
                marker.on('mouseover', function(e) {
                    this.setZIndexOffset(1000);
                    const el = e.target.getElement().querySelector('div');
                    if (el) el.style.transform = 'scale(1.2)';
                });

                marker.on('mouseout', function(e) {
                    this.setZIndexOffset(0);
                    const el = e.target.getElement().querySelector('div');
                    if (el) el.style.transform = 'scale(1)';
                });

                // ✅ Add click listener to zoom into the shop
                marker.on('click', function(e) {
                    map.flyTo(e.latlng, 18, {
                        animate: true,
                        duration: 1.5
                    });
                    // Open popup after a short delay to let zoom start
                    setTimeout(() => {
                        this.openPopup();
                    }, 500);
                });

                markers.push(marker);
            });


            /**
             * ✅ Center Maps Logic
             * Handles zooming based on number of markers
             */
            function centerMaps() {
                // Ensure map is correctly sized before actions
                map.invalidateSize();

                if (markers.length === 0) {
                    // No shops: Center Indonesia
                    map.setView([-2.5489, 118.0149], 5);
                } else if (markers.length === 1) {
                    // Single shop: Zoom specifically to it with smooth animation
                    const marker = markers[0];

                    // Use flyTo for smooth animation
                    map.flyTo(marker.getLatLng(), 18, {
                        animate: true,
                        duration: 3 // Slower for evident effect
                    });

                    map.once('moveend', function() {
                        marker.openPopup();
                    });
                } else {
                    // Multiple shops: Calculate bounds and Fly To them
                    const group = new L.featureGroup(markers);
                    const bounds = group.getBounds();

                    if (bounds.isValid()) {
                        setTimeout(() => {
                            // Calculate center and appropriate zoom
                            const center = bounds.getCenter();

                            // Safe zoom calculation: get bounds zoom and subtract 1 for padding
                            let targetZoom = map.getBoundsZoom(bounds) - 1;

                            // Cap zoom to prevent too close or too far
                            if (targetZoom > 18) targetZoom = 18;
                            if (targetZoom < 2) targetZoom = 2;

                            // Use flyTo for the "swoop" animation effect the user wants
                            map.flyTo(center, targetZoom, {
                                animate: true,
                                duration: 2.5 // Long duration for smooth flight
                            });
                        }, 500);
                    } else {
                        map.setView([-2.5489, 118.0149], 5);
                    }
                }
            }

            // Execute centering with a slight delay
            setTimeout(centerMaps, 500);
        }
    });
</script>
@endpush