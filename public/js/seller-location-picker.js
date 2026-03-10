
class SellerLocationPicker {
    constructor() {
        // Element DOM
        this.mapContainer = document.getElementById('shopMap');
        this.addressSearchInput = document.getElementById('address_search');
        this.addressStoreTextarea = document.getElementById('address_store');
        this.latitudeInput = document.getElementById('latitude');
        this.longitudeInput = document.getElementById('longitude');
        this.searchResultsDropdown = document.getElementById('searchResults');
        this.btnCurrentLocation = document.getElementById('btnCurrentLocation');
        this.btnSearchLocation = document.getElementById('btnSearchLocation');
        this.searchWrapper = document.getElementById('searchWrapper');

        // Map dan marker
        this.map = null;
        this.marker = null;
        this.searchMarkers = [];

        // Default lokasi (Jakarta)
        this.defaultLat = -6.2088;
        this.defaultLng = 106.8456;

        // Nominatim API endpoint
        this.nominatimUrl = 'https://nominatim.openstreetmap.org';

        // Initialize
        this.init();
    }

    /**
     * Initialize peta dan event listeners
     */
    init() {
        // Inisialisasi Leaflet map
        this.initMap();

        // Event listeners
        this.addressSearchInput.addEventListener('input', (e) => this.handleSearchInput(e));
        this.btnCurrentLocation.addEventListener('click', (e) => this.handleCurrentLocation(e));
        this.btnSearchLocation.addEventListener('click', (e) => this.handleSearchTab(e));
        document.addEventListener('click', (e) => this.handleDocumentClick(e));
    }

    /**
     * Inisialisasi Leaflet map dengan OpenStreetMap
     */
    initMap() {
        // Create map centered di Jakarta
        this.map = L.map(this.mapContainer).setView([this.defaultLat, this.defaultLng], 13);

        // Gunakan OpenStreetMap tile gratis
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Event: Click pada map untuk set marker
        this.map.on('click', (e) => this.handleMapClick(e));

        // CHECK for saved location
        if (this.latitudeInput && this.latitudeInput.value && this.longitudeInput && this.longitudeInput.value) {
            const lat = parseFloat(this.latitudeInput.value);
            const lng = parseFloat(this.longitudeInput.value);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                // Set marker and view
                this.setMarkerLocation(lat, lng);
            }
        }
    }

    /**
     * Handle klik tombol "Lokasi Saat Ini"
     * Gunakan HTML5 Geolocation API untuk mendapat koordinat real-time
     */
    handleCurrentLocation(e) {
        e.preventDefault();
        
        // Set button state
        this.btnCurrentLocation.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mendapatkan lokasi...';
        this.btnCurrentLocation.disabled = true;

        // Check if browser supports geolocation
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung Geolocation API. Silakan gunakan browser terbaru.');
            this.btnCurrentLocation.innerHTML = '<i class="fa fa-location-arrow"></i> Lokasi Saat Ini';
            this.btnCurrentLocation.disabled = false;
            return;
        }

        // Get current position dengan timeout yang lebih panjang
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Set marker dan update inputs
                this.setMarkerLocation(lat, lng);

                // Reset button
                this.btnCurrentLocation.innerHTML = '<i class="fa fa-location-arrow"></i> Lokasi Saat Ini';
                this.btnCurrentLocation.disabled = false;
            },
            (error) => {
                // Handle error
                let errorMessage = '';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin akses lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Permintaan lokasi timeout. Pastikan GPS aktif dan Anda memiliki sinyal yang kuat.';
                        break;
                    default:
                        errorMessage = 'Terjadi kesalahan saat mengakses lokasi.';
                }
                alert(errorMessage);

                // Reset button
                this.btnCurrentLocation.innerHTML = '<i class="fa fa-location-arrow"></i> Lokasi Saat Ini';
                this.btnCurrentLocation.disabled = false;
            },
            {
                // Options untuk geolocation
                enableHighAccuracy: false,
                timeout: 30000,
                maximumAge: 5000
            }
        );
    }

    /**
     * Handle klik tombol "Cari Lokasi" untuk switch ke tab search
     */
    handleSearchTab(e) {
        e.preventDefault();
        
        // Set active state
        this.btnSearchLocation.classList.add('active');
        this.btnCurrentLocation.classList.remove('active');
        
        // Show search wrapper
        this.searchWrapper.style.display = 'block';
        
        // Focus search input
        this.addressSearchInput.focus();
    }

    /**
     * Handle saat user klik di peta
     */
    handleMapClick(e) {
        const { lat, lng } = e.latlng;
        this.setMarkerLocation(lat, lng);
    }

    /**
     * Set marker dan update input fields
     */
    setMarkerLocation(lat, lng) {
        // Remove marker lama jika ada
        if (this.marker) {
            this.map.removeLayer(this.marker);
        }

        // Create marker baru dengan draggable
        this.marker = L.marker([lat, lng], { draggable: true })
            .addTo(this.map)
            .bindPopup(`
                <div class="leaflet-popup-content" style="font-size: 0.9rem;">
                    <small><strong>Latitude:</strong> ${lat.toFixed(6)}</small><br>
                    <small><strong>Longitude:</strong> ${lng.toFixed(6)}</small><br>
                    <small style="color: #666;">Seret marker untuk menyesuaikan posisi</small>
                </div>
            `)
            .openPopup();

        // Event: Saat marker di-drag
        this.marker.on('drag', () => this.updateFromMarker());
        this.marker.on('dragend', () => this.geocodeAddress());

        // Update input fields
        this.updateInputs(lat, lng);

        // Center map ke marker
        this.map.setView([lat, lng], 15);

        // Geocode untuk dapatkan nama alamat
        this.geocodeAddress();
    }

    /**
     * Update latitude & longitude inputs
     */
    updateInputs(lat, lng) {
        this.latitudeInput.value = lat.toFixed(8);
        this.longitudeInput.value = lng.toFixed(8);
    }

    /**
     * Update dari marker (saat di-drag)
     */
    updateFromMarker() {
        const { lat, lng } = this.marker.getLatLng();
        this.updateInputs(lat, lng);
    }

    /**
     * Geocode: Dapatkan nama alamat dari koordinat (reverse geocoding)
     * Menggunakan Nominatim API (gratis, tanpa API key)
     */
    geocodeAddress() {
        if (!this.marker) return;

        const { lat, lng } = this.marker.getLatLng();

        // Fetch reverse geocoding dari Nominatim
        fetch(`${this.nominatimUrl}/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.address) {
                    // Format alamat lengkap dari Nominatim
                    const addressText = this.formatAddressFromNominatim(data);
                    this.addressStoreTextarea.value = addressText;
                }
            })
            .catch(error => {
                console.error('Error geocoding:', error);
            });
    }

    /**
     * Format alamat dari Nominatim response
     * Susun komponeneleme menjadi alamat yang readable
     */
    formatAddressFromNominatim(data) {
        const address = data.address;
        const parts = [];

        // Prioritas urutan alamat
        if (address.house_number) parts.push(address.house_number);
        if (address.road) parts.push(address.road);
        if (address.suburb) parts.push(address.suburb);
        if (address.village) parts.push(address.village);
        if (address.city) parts.push(address.city);
        if (address.county) parts.push(address.county);
        if (address.postcode) parts.push(address.postcode);

        return parts.join(', ');
    }

    /**
     * Handle input pencarian alamat
     */
    handleSearchInput(e) {
        const query = e.target.value.trim();

        // Clear results jika input kosong
        if (query.length === 0) {
            this.searchResultsDropdown.style.display = 'none';
            this.clearSearchMarkers();
            return;
        }

        // Minimal 3 karakter untuk search
        if (query.length < 3) {
            this.searchResultsDropdown.style.display = 'none';
            return;
        }

        // Search menggunakan Nominatim
        this.searchAddress(query);
    }

    /**
     * Search alamat menggunakan Nominatim API
     */
    searchAddress(query) {
        // Nominatim search endpoint
        const url = `${this.nominatimUrl}/search?format=json&q=${encodeURIComponent(query)}&limit=8`;

        fetch(url)
            .then(response => response.json())
            .then(results => {
                this.displaySearchResults(results);
            })
            .catch(error => {
                console.error('Error searching address:', error);
                this.searchResultsDropdown.style.display = 'none';
            });
    }

    /**
     * Tampilkan hasil pencarian di dropdown
     */
    displaySearchResults(results) {
        this.clearSearchMarkers();
        this.searchResultsDropdown.innerHTML = '';

        if (results.length === 0) {
            this.searchResultsDropdown.innerHTML = '<div class="search-result-item" style="color: #999;">Tidak ada hasil ditemukan</div>';
            this.searchResultsDropdown.style.display = 'block';
            return;
        }

        // Buat item untuk setiap hasil
        results.forEach((result, index) => {
            const item = document.createElement('div');
            item.className = 'search-result-item';

            const name = document.createElement('div');
            name.className = 'search-result-item-name';
            name.textContent = result.name;

            const address = document.createElement('div');
            address.className = 'search-result-item-address';
            address.textContent = result.display_name.substring(0, 100) + '...';

            item.appendChild(name);
            item.appendChild(address);

            // Click handler untuk pilih lokasi
            item.addEventListener('click', () => {
                this.selectSearchResult(result);
            });

            this.searchResultsDropdown.appendChild(item);

            // Add marker untuk setiap hasil (semi-transparent)
            const searchMarker = L.marker([parseFloat(result.lat), parseFloat(result.lon)])
                .addTo(this.map)
                .setOpacity(0.5);

            this.searchMarkers.push(searchMarker);
        });

        this.searchResultsDropdown.style.display = 'block';
    }

    /**
     * Handle saat pilih hasil pencarian
     */
    selectSearchResult(result) {
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);

        // Set lokasi utama (primary marker)
        this.setMarkerLocation(lat, lng);

        // Update search input dengan nama lokasi
        this.addressSearchInput.value = result.name;

        // Clear dropdown
        this.searchResultsDropdown.style.display = 'none';
        this.clearSearchMarkers();
    }

    /**
     * Clear marker search (semi-transparent markers)
     */
    clearSearchMarkers() {
        this.searchMarkers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.searchMarkers = [];
    }

    /**
     * Handle click di document untuk tutup dropdown
     */
    handleDocumentClick(e) {
        // Jika click di luar search input dan dropdown, tutup dropdown
        if (!this.addressSearchInput.contains(e.target) && !this.searchResultsDropdown.contains(e.target)) {
            this.searchResultsDropdown.style.display = 'none';
        }
    }
}

// Inisialisasi saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new SellerLocationPicker();
});
