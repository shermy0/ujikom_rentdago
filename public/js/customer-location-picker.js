

class CustomerLocationPicker {
    constructor() {
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
            console.error('Leaflet.js tidak ditemukan! Pastikan Leaflet sudah di-load sebelum script ini.');
            return;
        }

        // Element DOM - dengan null checks
        this.mapContainer = document.getElementById('map');
        if (!this.mapContainer) {
            console.error('Map container tidak ditemukan! ID: "map"');
            return;
        }

        this.addressSearchInput = document.getElementById('address_search');
        this.addressTextarea = document.getElementById('addressInput');
        this.latitudeInput = document.getElementById('latitude');
        this.longitudeInput = document.getElementById('longitude');
        this.searchResultsDropdown = document.getElementById('searchResults');
        this.btnCurrentLocation = document.getElementById('btnCurrentLocation');
        this.btnSearchLocation = document.getElementById('btnSearchLocation');
        this.searchWrapper = document.getElementById('searchWrapper');

        // Check required elements
        if (!this.latitudeInput || !this.longitudeInput) {
            console.error('Latitude atau Longitude input tidak ditemukan!');
            return;
        }

        // Map dan marker
        this.map = null;
        this.marker = null;
        this.searchMarkers = [];

        // Default lokasi (Jakarta)
        this.defaultLat = -6.2088;
        this.defaultLng = 106.8456;

        // Nominatim API endpoint
        this.nominatimUrl = 'https://nominatim.openstreetmap.org';

        console.log('CustomerLocationPicker constructor initialized');

        // Initialize
        this.init();
    }

    /**
     * Initialize peta dan event listeners
     */
    init() {
        try {
            // Inisialisasi Leaflet map
            this.initMap();

            // Event listeners
            if (this.addressSearchInput) {
                this.addressSearchInput.addEventListener('input', (e) => this.handleSearchInput(e));
            }
            if (this.btnCurrentLocation) {
                this.btnCurrentLocation.addEventListener('click', (e) => this.handleCurrentLocation(e));
            }
            if (this.btnSearchLocation) {
                this.btnSearchLocation.addEventListener('click', (e) => this.handleSearchTab(e));
            }
            document.addEventListener('click', (e) => this.handleDocumentClick(e));
            
            console.log('CustomerLocationPicker initialized successfully');
        } catch (error) {
            console.error('Error initializing CustomerLocationPicker:', error);
        }
    }

    /**
     * Inisialisasi Leaflet map dengan OpenStreetMap
     */
    initMap() {
        try {
            console.log('initMap() called');
            console.log('Map container:', this.mapContainer);
            console.log('Map container computed style:', window.getComputedStyle(this.mapContainer));
            
            // Pastikan map container visible dan punya ukuran
            this.mapContainer.style.display = 'block';
            this.mapContainer.style.height = '350px';
            this.mapContainer.style.width = '100%';
            
            console.log('After style set - height:', this.mapContainer.offsetHeight, 'width:', this.mapContainer.offsetWidth);

            // Create map centered di Jakarta
            console.log('Creating map with L.map...');
            this.map = L.map(this.mapContainer, {
                preferCanvas: false,
                zoomControl: true
            }).setView([this.defaultLat, this.defaultLng], 13);

            console.log('Map created:', this.map);

            // Gunakan OpenStreetMap tile gratis
            const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            console.log('Tiles added to map');

            // Invalidate size untuk ensure map render correctly
            setTimeout(() => {
                this.map.invalidateSize();
                console.log('Map size invalidated');
            }, 300);

            // Event: Click pada map untuk set marker
            this.map.on('click', (e) => this.handleMapClick(e));

            // Load existing marker jika ada data lama
            if (this.latitudeInput && this.latitudeInput.value && this.longitudeInput && this.longitudeInput.value) {
                const lat = parseFloat(this.latitudeInput.value);
                const lng = parseFloat(this.longitudeInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    console.log('Loading existing marker:', lat, lng);
                    // Add small delay to ensure map is ready before zooming
                    setTimeout(() => {
                        this.setMarkerLocation(lat, lng);
                        this.map.setView([lat, lng], 18); // Force high zoom
                    }, 500);
                }
            }

            console.log('Map initialized successfully');
        } catch (error) {
            console.error('Error initializing map:', error);
            console.error('Stack:', error.stack);
            // Try to show alternative error message
            if (this.mapContainer) {
                this.mapContainer.innerHTML = '<div style="padding: 1rem; color: red; font-weight: bold;">Error: ' + error.message + '</div>';
            }
        }
    }

    /**
     * Handle klik tombol "Lokasi Saat Ini"
     * Gunakan HTML5 Geolocation API untuk mendapat koordinat real-time
     */
    handleCurrentLocation(e) {
        try {
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
        } catch (error) {
            console.error('Error in handleCurrentLocation:', error);
            if (this.btnCurrentLocation) {
                this.btnCurrentLocation.innerHTML = '<i class="fa fa-location-arrow"></i> Lokasi Saat Ini';
                this.btnCurrentLocation.disabled = false;
            }
        }
    }

    /**
     * Handle klik tombol "Cari Lokasi" untuk switch ke tab search
     */
    handleSearchTab(e) {
        try {
            e.preventDefault();
            
            // Set active state
            if (this.btnSearchLocation) {
                this.btnSearchLocation.classList.add('active');
            }
            if (this.btnCurrentLocation) {
                this.btnCurrentLocation.classList.remove('active');
            }
            
            // Show search wrapper
            if (this.searchWrapper) {
                this.searchWrapper.style.display = 'block';
            }
            
            // Focus search input
            if (this.addressSearchInput) {
                this.addressSearchInput.focus();
            }
        } catch (error) {
            console.error('Error in handleSearchTab:', error);
        }
    }

    /**
     * Handle saat user klik di peta
     */
    handleMapClick(e) {
        try {
            const { lat, lng } = e.latlng;
            this.setMarkerLocation(lat, lng);
        } catch (error) {
            console.error('Error in handleMapClick:', error);
        }
    }

    /**
     * Set marker dan update input fields
     */
    setMarkerLocation(lat, lng) {
        try {
            if (!this.map) {
                console.error('Map is not initialized');
                return;
            }

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
        } catch (error) {
            console.error('Error setting marker location:', error);
        }
    }

    /**
     * Update latitude & longitude inputs
     */
    updateInputs(lat, lng) {
        try {
            if (this.latitudeInput) {
                this.latitudeInput.value = lat.toFixed(8);
            }
            if (this.longitudeInput) {
                this.longitudeInput.value = lng.toFixed(8);
            }
        } catch (error) {
            console.error('Error updating inputs:', error);
        }
    }

    /**
     * Update dari marker (saat di-drag)
     */
    updateFromMarker() {
        try {
            if (!this.marker) return;
            const { lat, lng } = this.marker.getLatLng();
            this.updateInputs(lat, lng);
        } catch (error) {
            console.error('Error updating from marker:', error);
        }
    }

    /**
     * Geocode: Dapatkan nama alamat dari koordinat (reverse geocoding)
     * Menggunakan Nominatim API (gratis, tanpa API key)
     */
    geocodeAddress() {
        try {
            if (!this.marker) return;

            const { lat, lng } = this.marker.getLatLng();

            // Fetch reverse geocoding dari Nominatim
            fetch(`${this.nominatimUrl}/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        // Format alamat lengkap dari Nominatim
                        const addressText = this.formatAddressFromNominatim(data);
                        if (this.addressTextarea && this.addressTextarea.value === '') {
                            this.addressTextarea.value = addressText;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error geocoding:', error);
                });
        } catch (error) {
            console.error('Error in geocodeAddress:', error);
        }
    }

    /**
     * Format alamat dari Nominatim response
     * Susun komponen menjadi alamat yang readable
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
        try {
            if (!this.searchResultsDropdown) return;
            
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
        } catch (error) {
            console.error('Error handling search input:', error);
        }
    }

    /**
     * Search alamat menggunakan Nominatim API
     */
    searchAddress(query) {
        try {
            // Nominatim search endpoint
            const url = `${this.nominatimUrl}/search?format=json&q=${encodeURIComponent(query)}&limit=8`;

            fetch(url)
                .then(response => response.json())
                .then(results => {
                    this.displaySearchResults(results);
                })
                .catch(error => {
                    console.error('Error searching address:', error);
                    if (this.searchResultsDropdown) {
                        this.searchResultsDropdown.style.display = 'none';
                    }
                });
        } catch (error) {
            console.error('Error in searchAddress:', error);
        }
    }

    /**
     * Tampilkan hasil pencarian di dropdown
     */
    displaySearchResults(results) {
        try {
            if (!this.searchResultsDropdown || !this.map) return;
            
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
        } catch (error) {
            console.error('Error displaying search results:', error);
        }
    }

    /**
     * Handle saat pilih hasil pencarian
     */
    selectSearchResult(result) {
        try {
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            // Set lokasi utama (primary marker)
            this.setMarkerLocation(lat, lng);

            // Update search input dengan nama lokasi
            if (this.addressSearchInput) {
                this.addressSearchInput.value = result.name;
            }

            // Clear dropdown
            if (this.searchResultsDropdown) {
                this.searchResultsDropdown.style.display = 'none';
            }
            this.clearSearchMarkers();
        } catch (error) {
            console.error('Error selecting search result:', error);
        }
    }

    /**
     * Clear marker search (semi-transparent markers)
     */
    clearSearchMarkers() {
        try {
            this.searchMarkers.forEach(marker => {
                if (this.map && marker) {
                    this.map.removeLayer(marker);
                }
            });
            this.searchMarkers = [];
        } catch (error) {
            console.error('Error clearing search markers:', error);
        }
    }

    /**
     * Handle click di document untuk tutup dropdown
     */
    handleDocumentClick(e) {
        // Jika click di luar search input dan dropdown, tutup dropdown
        if (this.searchResultsDropdown && this.addressSearchInput) {
            if (!this.addressSearchInput.contains(e.target) && !this.searchResultsDropdown.contains(e.target)) {
                this.searchResultsDropdown.style.display = 'none';
            }
        }
    }
}

// Inisialisasi saat DOM ready atau window load
function initCustomerLocationPicker() {
    if (typeof L === 'undefined') {
        console.warn('Leaflet.js belum ter-load, akan retry...');
        setTimeout(initCustomerLocationPicker, 500);
        return;
    }
    new CustomerLocationPicker();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomerLocationPicker);
} else if (document.readyState === 'interactive') {
    setTimeout(initCustomerLocationPicker, 100);
} else {
    // DOM sudah loaded
    initCustomerLocationPicker();
}

// Also try on window load
window.addEventListener('load', function() {
    // Ensure it's initialized
    if (typeof CustomerLocationPicker !== 'undefined') {
        console.log('Window loaded, CustomerLocationPicker available');
    }
});
