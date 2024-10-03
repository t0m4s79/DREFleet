import { useEffect } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import "@geoman-io/leaflet-geoman-free";
import "@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css";

export default function OrderRoutePolygon( {onAreaChange, color} ) {
    console.log(color)

    useEffect(() => {
        const map = L.map('map').setView([32.6443385, -16.9167589], 13);

        // map.pm.setPathOptions({
        //     color: color,
        //     fillColor: color,
        //     fillOpacity: 0.4,
        //   });

        // Add tile layer to the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        // Initialize Leaflet-Geoman controls
        map.pm.addControls({
            position: 'topleft', // Position of the control buttons
            drawPolygon: true, // Enable polygon drawing
            drawMarker: false, // Disable marker tool
            drawPolyline: false, // Disable polyline tool
            drawCircle: false, // Disable circle tool
            drawRectangle: false, // Disable rectangle tool
            editMode: true, // Enable editing mode for shapes
            dragMode: false, // Disable drag mode for markers/shapes
            cutPolygon: false, // Disable cutting polygons
            removalMode: true, // Enable delete mode for shapes
        });
        map.pm.setLang("pt-br");

        // Listen for the creation of new polygons
        map.on('pm:create', (e) => {
            const layer = e.layer;
            const polygonCoordinates = layer.getLatLngs();
            console.log('Polygon coordinates:', polygonCoordinates);
            onAreaChange(polygonCoordinates);
        });

        // Center map on specific coordinates
        map.setView([32.6443385, -16.9167589], 10);

    }, []);

    return (
        <div id="map" style={{ height: '500px', width: '100%' }} />
    );
};