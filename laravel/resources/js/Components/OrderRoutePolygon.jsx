import { MapContainer, TileLayer, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import { useEffect, useRef } from 'react';
import L from 'leaflet';
import "@geoman-io/leaflet-geoman-free";
import "@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css";

{/*TODO: STORE THE POLYGON COORDINATES CORRECTLY AFTER DRAGGING THE VERTICES*/}
function GeomanControls({ onAreaChange, color, bounds, initialCoordinates }) {
    const map = useMap();
    const polygonLayerRef = useRef(null);

    useEffect(() => {
        // Add Leaflet-Geoman controls
        map.pm.addControls({
            position: 'topleft',
            drawPolygon: true,
            drawMarker: false,
            drawPolyline: false,
            drawCircle: false,
            drawRectangle: false,
            editMode: true,
            dragMode: true,
            cutPolygon: false,
            removalMode: true,
        });
        map.pm.setLang('pt-br');

        // Remove existing polygon before creating a new one
        const removeExistingPolygon = () => {
            if (polygonLayerRef.current) {
                map.removeLayer(polygonLayerRef.current);
                polygonLayerRef.current = null;
            }
        };

        map.on('pm:create', (e) => {
            removeExistingPolygon(); // Ensure only one polygon exists

            const layer = e.layer;
            const polygonCoordinates = layer.getLatLngs();
            polygonLayerRef.current = layer; // Save reference to the polygon

            // Apply color to the polygon
            layer.setStyle({
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            });

            // Allow vertex deletion during edit mode
            layer.pm.enable({
                allowSelfIntersection: false, // Disable self-intersection to allow deleting vertices
                snappable: true, // Snapping makes interaction easier
            });

            // Check if the polygon is within the boundary
            if (bounds && !bounds.contains(layer.getBounds())) {
                map.removeLayer(layer); // Remove the polygon if it's outside the boundary
                alert('Área fora das coordenadas permitidas!');
            } else {
                onAreaChange(polygonCoordinates); // Send coordinates back to parent
            }
        });

        // Load initial polygon if coordinates are provided
        if (initialCoordinates && initialCoordinates.length > 0) {
            removeExistingPolygon(); // Ensure no duplicate polygons

            const initialPolygon = L.polygon(initialCoordinates, {
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            }).addTo(map);

            polygonLayerRef.current = initialPolygon;

            // Enable editing and vertex deletion for existing polygons
            initialPolygon.pm.enable({
                allowSelfIntersection: false, // Allow vertex deletion
                snappable: true,
            });
        }

    }, [map, color, bounds, onAreaChange, initialCoordinates]);

    useEffect(() => {
        // Update the polygon color whenever the color changes
        if (polygonLayerRef.current) {
            polygonLayerRef.current.setStyle({
                color: color,
                fillColor: color,
                fillOpacity: 0.4,
            });
        }
    }, [color]); // This effect runs whenever the color changes

    return null;
}

// Main component to render the map
export default function OrderRoutePolygon({ onAreaChange, color, initialCoordinates }) {
    // Define the boundary as LatLngBounds (southwest corner and northeast corner)
    const bounds = L.latLngBounds(
        [32.269181, -17.735033], // Southwest boundary
        [33.350247, -15.861279]  // Northeast boundary
    );

    return (
        <MapContainer
            center={[32.6443385, -16.9167589]}
            zoom={13}
            style={{ height: '500px', width: '100%' }}
            maxBounds={bounds} // Limit map panning and zooming to this area
            maxBoundsViscosity={1.0} // Make boundary strict
        >
            <TileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution="&copy; OpenStreetMap contributors"
            />
            {/* GeomanControls to manage drawing and polygon creation */}
            <GeomanControls
                onAreaChange={onAreaChange}
                color={color}
                bounds={bounds}
                initialCoordinates={initialCoordinates} // Pass initial coordinates here
            />
        </MapContainer>
    );
}
