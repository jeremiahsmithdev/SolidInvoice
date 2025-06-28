import { Controller } from '@hotwired/stimulus';
import * as L from 'leaflet';

interface ClientMapData {
    id: string;
    name: string;
    email: string;
    address: {
        street1: string;
        street2: string;
        city: string;
        state: string;
        zip: string;
        country: string;
        countryName: string;
        formatted: string;
    };
}

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
    private clientMarkers: L.Marker[] = [];

    connect() {
        this.initializeMap();
        this.loadClientMarkers();
    }

    disconnect() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    }

    private async initializeMap() {
        // Initialize the map
        this.map = L.map(this.element as HTMLElement).setView(
            [this.centerLatValue, this.centerLngValue], 
            this.zoomValue
        );

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);
    }

    private async loadClientMarkers() {
        try {
            const response = await fetch('/map/api/clients');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const clients: ClientMapData[] = await response.json();
            await this.addClientMarkersToMap(clients);
        } catch (error) {
            console.error('Failed to load client data:', error);
        }
    }

    private async addClientMarkersToMap(clients: ClientMapData[]) {
        for (const client of clients) {
            try {
                const coordinates = await this.geocodeAddress(client.address.formatted);
                if (coordinates) {
                    const marker = L.marker(coordinates)
                        .addTo(this.map!)
                        .bindPopup(this.createClientPopupContent(client));
                    
                    this.clientMarkers.push(marker);
                }
            } catch (error) {
                console.warn(`Failed to geocode address for client ${client.name}:`, error);
            }
        }

        if (this.clientMarkers.length > 0) {
            const group = new L.FeatureGroup(this.clientMarkers);
            this.map!.fitBounds(group.getBounds().pad(0.1));
        }
    }

    private async geocodeAddress(address: string): Promise<[number, number] | null> {
        if (!address.trim()) {
            return null;
        }

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`
            );
            
            if (!response.ok) {
                throw new Error(`Geocoding failed with status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.length > 0) {
                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            }
            
            return null;
        } catch (error) {
            console.warn('Geocoding error:', error);
            return null;
        }
    }

    private createClientPopupContent(client: ClientMapData): string {
        return `
            <div class="client-popup">
                <h5 class="mb-2">${this.escapeHtml(client.name)}</h5>
                <p class="mb-1">
                    <strong>Email:</strong> 
                    <a href="mailto:${this.escapeHtml(client.email)}">${this.escapeHtml(client.email)}</a>
                </p>
                <p class="mb-1">
                    <strong>Address:</strong><br>
                    ${this.escapeHtml(client.address.formatted)}
                </p>
                <a href="/clients/${client.id}" class="btn btn-sm btn-primary mt-2">
                    View Client
                </a>
            </div>
        `;
    }

    private escapeHtml(text: string): string {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}