import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import "@geoman-io/leaflet-geoman-free";
import "@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css";

function GeomanControls({ onAreaChange, color, bounds, initialCoordinates }) {
    const map = useMap();
    const [polygonLayer, setPolygonLayer] = useState(null);
    const [polygonExists, setPolygonExists] = useState(false); // Flag to track polygon existence

    useEffect(() => {
        // Initialize Leaflet-Geoman
        map.pm.addControls({
            position: 'topleft',
            drawPolygon: true,
            drawMarker: false,
            drawPolyline: false,
            drawCircle: false,
            drawRectangle: false,
            editMode: true,
            dragMode: false,
            cutPolygon: true,
        });
        map.pm.setLang('pt_br');

        // Handle polygon creation
        const handlePolygonCreation = (layer) => {
            // If there is an existing polygon, remove it
            if (polygonLayer) {
                map.removeLayer(polygonLayer);
            }

            // Apply styles to the newly created polygon
            layer.setStyle({
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            });

            // Check if the polygon is within bounds
            if (!bounds.contains(layer.getBounds())) {
                map.removeLayer(layer); // Remove the polygon if it's outside the boundary
                alert('Área fora das coordenadas permitidas!');
                return;
            }

            // Save the reference to the polygon layer
            setPolygonLayer(layer);
            setPolygonExists(true); // Set flag to true

            // Send coordinates back to parent
            const coordinates = layer.getLatLngs();
            onAreaChange(coordinates);

            // Add event listener for editing
            layer.on('pm:edit', () => {
                console.log('Polygon edited:', layer.getLatLngs());

                // Check if the polygon is within bounds
                if (!bounds.contains(layer.getBounds())) {
                    map.removeLayer(layer); // Remove the polygon if it's outside the boundary
                    alert('Área fora das coordenadas permitidas!');
                    return;
                }

                // Send updated coordinates back to parent
                onAreaChange(layer.getLatLngs());
            });

            // Add event listener for removal
            layer.on('pm:remove', () => {
                setPolygonLayer(null); // Clear the reference on removal
                setPolygonExists(false); // Set flag to false
                onAreaChange(null); // Send null to indicate no polygon exists
            });
        };

        map.on('pm:create', (e) => {
            handlePolygonCreation(e.layer);
        });

        // Cleanup on component unmount
        return () => {
            map.off('pm:create');
        };
    }, [map, color, polygonLayer, onAreaChange, bounds]);

    useEffect(() => {
        // Draw the initial polygon if coordinates are provided and polygon doesn't already exist
        if (initialCoordinates && initialCoordinates.length > 0 && !polygonExists) {
            const initialPolygon = L.polygon(initialCoordinates, {
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            }).addTo(map);

            // Set the polygon layer reference
            setPolygonLayer(initialPolygon);
            setPolygonExists(true); // Set flag to true

            // Enable editing on the initial polygon
            initialPolygon.pm.enable({
                allowSelfIntersection: true,
                snappable: true,
            });

            // Add event listener for editing
            initialPolygon.on('pm:edit', () => {
                console.log('Polygon edited:', initialPolygon.getLatLngs());

                // Check bounds on edit
                if (!bounds.contains(initialPolygon.getBounds())) {
                    alert('Área fora das coordenadas permitidas!');
                    return;
                }

                // Send updated coordinates back to parent
                onAreaChange(initialPolygon.getLatLngs());
            });

            // Add event listener for removal
            initialPolygon.on('pm:remove', () => {
                setPolygonLayer(null); // Clear the reference on removal
                setPolygonExists(false); // Set flag to false
                onAreaChange(null);
            });
        }
    }, [initialCoordinates, map, color, onAreaChange, bounds, polygonExists]);

    useEffect(() => {
        // Update the polygon color dynamically when the color prop changes
        if (polygonLayer) {
            polygonLayer.setStyle({
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            });
        }
    }, [color, polygonLayer]); // Update when color or polygonLayer changes
}

export default function OrderRoutePolygon({ onAreaChange, color, initialCoordinates }) {

    const bounds = L.latLngBounds(
        [32.269181, -17.735033], // Southwest boundary
        [33.350247, -15.861279]  // Northeast boundary
    );

    return (
        <MapContainer
            center={[32.6443385, -16.9167589]}
            maxBounds={bounds} // Limit map panning and zooming to this area
            maxBoundsViscosity={1.0} // Make boundary strict
            zoom={13}
            style={{ height: '500px', width: '100%' }}
        >
            <TileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution="&copy; OpenStreetMap contributors"
            />
            <GeomanControls
                onAreaChange={onAreaChange}
                color={color}
                bounds={bounds}
                initialCoordinates={initialCoordinates} // Pass initial coordinates here
            />
        </MapContainer>
    );
};
