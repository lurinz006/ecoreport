/**
 * map_handler.js
 * Handles Leaflet.js integration for Barangay EcoReport
 */

class EcoMap {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = Object.assign({
            defaultCenter: [6.9189, 122.0911], // Default to Guiwan, Zamboanga City
            defaultZoom: 13,
            interactive: true
        }, options);
        
        this.map = null;
        this.marker = null;
        this.init();
    }

    init() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        this.map = L.map(this.containerId).setView(this.options.defaultCenter, this.options.defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        if (this.options.interactive) {
            this.map.on('click', (e) => {
                this.updateMarker(e.latlng.lat, e.latlng.lng);
            });
        }
    }

    updateMarker(lat, lng, center = false) {
        if (this.marker) {
            this.marker.setLatLng([lat, lng]);
        } else {
            this.marker = L.marker([lat, lng], { draggable: this.options.interactive }).addTo(this.map);
            
            if (this.options.interactive) {
                this.marker.on('dragend', (e) => {
                    const position = this.marker.getLatLng();
                    this.onLocationSelected(position.lat, position.lng);
                });
            }
        }

        if (center) {
            this.map.setView([lat, lng], 16);
        }

        this.onLocationSelected(lat, lng);
    }

    onLocationSelected(lat, lng) {
        // To be overridden or used to update hidden inputs
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
        
        // Optional: Trigger reverse geocoding if needed
        // this.reverseGeocode(lat, lng);
    }

    addMarker(lat, lng, popupText = '') {
        const marker = L.marker([lat, lng]).addTo(this.map);
        if (popupText) {
            marker.bindPopup(popupText);
        }
        return marker;
    }

    invalidateSize() {
        if (this.map) {
            setTimeout(() => this.map.invalidateSize(), 200);
        }
    }
}
