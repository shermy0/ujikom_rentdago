/**
 * Courier Tracking System v2
 * Handles Leaflet map initialization, routing, and real-time GPS tracking with auto-arrival detection.
 */

window.CourierTracking = {
    map: null,
    routingControl: null,
    markerCourier: null,
    markerCustomer: null,
    watchId: null,
    lastGpsUpdate: 0,
    config: {
        orderId: null,
        csrfToken: null, 
        updateUrl: null,
        gpsThrottle: 5000,
        isTrackingActive: false,
        shipmentStatus: '',
        mapData: {}
    },

    init: function(config) {
        this.config = { ...this.config, ...config };
        
        console.log("Initializing Courier Tracking for Order #" + this.config.orderId);
        
        if (typeof L === 'undefined') {
            alert("Gagal memuat peta (Leaflet tidak ditemukan).");
            return;
        }

        this.initMap();

        // Auto-start tracking if already active in backend
        if (this.config.isTrackingActive == '1' || this.config.isTrackingActive === true) {
            this.startGpsEngine(); 
        }
    },

    initMap: function() {
        const mapData = this.config.mapData;
        
        // 1. Initialize Map instance
        this.map = L.map('deliveryMap').setView([mapData.shop.lat, mapData.shop.lng], 13);
        
        // 2. Add Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.map);

        // 3. Setup Custom Icons
        const courierIcon = L.divIcon({
            className: 'custom-icon shop-icon',
            html: '<i class="fa fa-truck"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });

        const customerIcon = L.divIcon({
            className: 'custom-icon customer-icon',
            html: '<i class="fa fa-location-dot"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });

        // 4. Create and Store Marker Instances
        this.markerCourier = L.marker([mapData.shop.lat, mapData.shop.lng], {
            icon: courierIcon,
            zIndexOffset: 1000
        }).addTo(this.map).bindPopup('<b>🚚 Lokasi Saya (Kurir)</b>');

        this.markerCustomer = L.marker([mapData.customer.lat, mapData.customer.lng], {
            icon: customerIcon
        }).addTo(this.map).bindPopup(`<b>📍 Customer: ${mapData.customer.name}</b><br>${mapData.customer.address}`);

        // 5. Initialize Routing Control
        // PRODUKSI: Menggunakan Mapbox (Original Token dari config)
        const mapboxApiKey = this.config.mapboxToken || 'pk.eyJ1IjoiYXJ0aHVyLWlhIiwiYSI6ImNsMG05Z3Z6ZTAwN2YzaW56a3Z6N3Z6a3YifQ.Q1Z6-Q1Z6-Q1Z6';

        this.routingControl = L.Routing.control({
            waypoints: [
                L.latLng(mapData.shop.lat, mapData.shop.lng),
                L.latLng(mapData.customer.lat, mapData.customer.lng)
            ],
            router: L.Routing.mapbox(mapboxApiKey), // Gunakan Mapbox Router
            lineOptions: {
                styles: [{ color: '#3b82f6', opacity: 1, weight: 6 }]
            },
            createMarker: function() { return null; },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: false
        }).addTo(this.map);

        // Auto fit bounds
        const group = L.featureGroup([this.markerCourier, this.markerCustomer]);
        this.map.fitBounds(group.getBounds().pad(0.2));

        // Fix blank map issue
        setTimeout(() => {
            this.map.invalidateSize();
        }, 500);
    },

    startGpsEngine: function() {
        if (this.watchId) return;

        console.log("🚀 GPS Engine Started");

        this.watchId = navigator.geolocation.watchPosition(
            (pos) => this.handleGpsUpdate(pos),
            (err) => this.handleGpsError(err),
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
        );
    },

    handleGpsUpdate: function(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const now = Date.now();

        if (now - this.lastGpsUpdate > this.config.gpsThrottle) {
            this.lastGpsUpdate = now;
            const newLatLng = new L.LatLng(lat, lng);

            // 1. Update Marker Position (ALWAYS)
            this.markerCourier.setLatLng(newLatLng);
            
            // 2. Auto-follow (pan map) only if NOT arrived
            if (this.config.shipmentStatus !== 'arrived') {
                if (!this.map.getBounds().contains(newLatLng)) {
                    this.map.panTo(newLatLng);
                }
            }

            // 3. Sync Backend
            this.updateBackendLocation(lat, lng);
        }
    },

    updateBackendLocation: function(lat, lng) {
        fetch(this.config.updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            body: JSON.stringify({
                order_id: this.config.orderId,
                lat: lat,
                lng: lng
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. Logic Jarak Menengah (< 100m): Munculkan tombol manual
                const btnManual = document.getElementById('btnManualArrived');
                const placeholder = document.getElementById('distancePlaceholder');

                if (data.distance !== null && this.config.shipmentStatus !== 'arrived') {
                    // Update Teks Jarak
                    const distDisplay = document.getElementById('currentDistanceDisplay');
                    const distText = document.getElementById('currentDistanceText');
                    
                    if (distDisplay && distText) {
                        distDisplay.style.display = 'block';
                        distText.innerText = Math.round(data.distance);
                    }

                    if (data.distance < 100) {
                        if (btnManual) btnManual.style.display = 'block';
                        if (placeholder) placeholder.style.display = 'none';
                    } else {
                        if (btnManual) btnManual.style.display = 'none';
                        if (placeholder) placeholder.style.display = 'block';
                    }
                }

                // 2. Logic Jarak Dekat (< 20m): Auto-Arrival
                if (data.arrived && this.config.shipmentStatus !== 'arrived') {
                    console.log("📍 You have arrived!");
                    this.config.shipmentStatus = 'arrived';
                    this.updateUIForArrived();
                }
            }
        })
        .catch(e => console.error("Update failed", e));
    },

    updateUIForArrived: function() {
        const badge = document.getElementById('statusBadge');
        if (badge) {
            badge.innerHTML = 'Sudah Sampai';
            badge.style.background = '#d1fae5';
            badge.style.color = '#065f46';
        }

        const container = document.getElementById('courierActionsContainer');
        if (container) {
            // Langsung munculkan tombol foto
            const shipmentId = this.config.mapData.shipment ? this.config.mapData.shipment.id : '';
            const photoUrl = shipmentId ? `/kurir/delivery-photo/${shipmentId}` : '/kurir/delivery-photo';

            container.innerHTML = `
                <button id="btnMainAction" 
                    onclick="window.location.href='${photoUrl}'" 
                    style="width: 100%; background: linear-gradient(135deg, #22c55e, #16a34a); 
                    color: white; border: none; padding: 20px; border-radius: 12px; 
                    font-size: 18px; font-weight: 800; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
                    animation: slideUp 0.4s ease-out;">
                    <i class="fa fa-camera"></i> AMBIL FOTO BUKTI
                </button>
            `;
        }
    },

    handleGpsError: function(err) {
        console.warn("GPS Error: " + err.message);
    }
};

// Global Helpers called from Blade
window.handleStartTrip = function(orderId) {
    if (!confirm('Mulai perjalanan sekarang?')) return;

    fetch(`/kurir/start-trip/${orderId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.CourierTracking.config.csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        console.log("Start Trip Response:", data);
        if (data.status === 'success' || data.success === true) {
            // Update UI instead of Reload
            const container = document.getElementById('courierActionsContainer');
            if (container) {
                container.innerHTML = `
                <div id="onTheWayContainer" style="display: flex; flex-direction: column; gap: 10px;">
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
                </div>`;
            }

            // Update Badge
            const badge = document.getElementById('statusBadge');
            if (badge) {
                badge.className = 'badge';
                badge.style.background = '#fef3c7';
                badge.style.color = '#92400e';
                badge.style.padding = '4px 12px';
                badge.style.borderRadius = '12px';
                badge.style.fontSize = '11px';
                badge.style.fontWeight = '600';
                badge.innerText = 'Sedang Dikirim';
            }

            // Start Tracking Engine immediately
            window.CourierTracking.config.shipmentStatus = 'on_the_way';
            window.CourierTracking.config.isTrackingActive = true;
            window.CourierTracking.startGpsEngine();

        } else {
            alert(data.message || 'Gagal memulai perjalanan');
        }
    });
};

window.handleConfirmArrived = function(orderId) {
    if (!confirm('Konfirmasi sudah sampai di lokasi?')) return;

    fetch(`/kurir/confirm-arrived/${orderId}`, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': window.CourierTracking.config.csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.reload();
        }
    })
    .catch(err => {
        console.error('Error:', err);
        window.location.reload();
    });
};

window.handleHandOver = function(orderId) {
    if (!confirm('Konfirmasi penyerahan barang ke customer?')) return;

    const btn = document.getElementById('btnMainAction');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';

    fetch(`/kurir/hand-over`, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CourierTracking.config.csrfToken 
        },
        body: JSON.stringify({ order_id: orderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Gagal penyerahan barang');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(e => {
        alert('Terjadi kesalahan: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
};
