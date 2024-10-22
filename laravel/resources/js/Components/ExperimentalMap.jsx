import React, { useEffect, useRef } from 'react';
import { MapContainer, Polygon, TileLayer, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L, { latLng } from 'leaflet';
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';
import { calculateTravelMetrics } from '@/utils/calculateTravelMetrics';

const boundsSouthWestCorner = [32.269181, -17.735033];
const boundsNorthEastCorner = [33.350247, -15.861279];

function Routing({ waypoints, onTrajectoryChange, updateSummary, updateWaypointData }) {
    //console.log(waypoints)
    const map = useMap();

    useEffect(() => {
        // Define the bounds (southwest and northeast corners)
        const bounds = L.latLngBounds(
            boundsSouthWestCorner, // Southwest corner
            boundsNorthEastCorner  // Northeast corner
        );

        // Set max bounds to restrict map area
        map.setMaxBounds(bounds);
        map.on('drag', function() {
            map.panInsideBounds(bounds);
        });

        if (!map || waypoints.length < 2) return;

        // Check if the OSRM server URL is defined in the environment variable
        const osrmServerUrl = import.meta.env.VITE_OSRM_SERVER_URL;
        
        const routerOptions = osrmServerUrl
            ? { serviceUrl: osrmServerUrl } // If the variable exists, use the OSRM server
            : {}; // If not, fall back to the default configuration

        const routingControl = L.Routing.control({
            waypoints: waypoints.map(wp => L.latLng(wp.lat, wp.lng)),
            router: L.Routing.osrmv1(routerOptions), // Use the conditional router
            language: 'pt-PT',
            draggableWaypoints: false, // Disable marker dragging
        }).addTo(map);

        routingControl.on('routesfound', function (e) {
            console.log('routes found ', e.routes)                      // TODO: CHECK THIS CONSOLE.LOG
            const trajectory = e.routes[0].coordinates;
            const summary = e.routes[0].summary;
            const instructions = e.routes[0].instructions;

            const metrics = calculateTravelMetrics(instructions, waypoints);
            console.log('metrics', metrics)
            // Pass the trajectory waypoints back to the form through the callback
            onTrajectoryChange(trajectory);
            updateWaypointData(metrics);
            updateSummary(summary);
        });

        return () => map.removeControl(routingControl);
    }, [map, waypoints]);

    return null;
}

export default function ExperimentalMap({ waypoints, onTrajectoryChange, updateSummary, updateWaypointData, route }) {
    //console.log(waypoints)
    let polyCoords
    if(route) {
        polyCoords = route.area.coordinates[0].map((coords)=>{
            return latLng({
                lat: coords[1],
                lng: coords[0]
            })
        })
        console.log(polyCoords)
    }

    return (
        <MapContainer center={[32.6443385, -16.9167589]} zoom={12} style={{ height: '500px', width: '100%' }}>
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Routing waypoints={waypoints} onTrajectoryChange={onTrajectoryChange} updateSummary={updateSummary} updateWaypointData={updateWaypointData}/>

            {route && (
                <Polygon 
                    positions={polyCoords}
                    pathOptions={{ color: route.area_color, fillOpacity: 0.1 }}
                />
            )}
        </MapContainer>
    );
}
