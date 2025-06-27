import { Controller } from '@hotwired/stimulus';
import * as L from 'leaflet';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        centerLat: Number,
        centerLng: Number,
        zoom: Number
    };

    declare readonly centerLatValue: number;
    declare readonly centerLngValue: number;
    declare readonly zoomValue: number;

    private map: L.Map | null = null;

    connect() {
        this.initializeMap();
    }

    disconnect() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    }

    private initializeMap() {
        // Initialize the map
        this.map = L.map(this.element as HTMLElement).setView(
            [this.centerLatValue, this.centerLngValue], 
            this.zoomValue
        );

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Add a sample marker
        L.marker([this.centerLatValue, this.centerLngValue])
            .addTo(this.map)
            .bindPopup('Welcome to SolidInvoice Map!')
            .openPopup();

        // Add click event to add markers
        this.map.on('click', (e: L.LeafletMouseEvent) => {
            L.marker(e.latlng)
                .addTo(this.map!)
                .bindPopup(`Clicked at ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`);
        });
    }
}