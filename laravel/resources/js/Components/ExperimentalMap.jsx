import React, { useEffect, useRef } from 'react';
import { MapContainer, TileLayer, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import 'leaflet-routing-machine';

const boundsSouthWestCorner = [32.269181, -17.735033];
const boundsNorthEastCorner = [33.350247, -15.861279];

function Routing({ waypoints, onTrajectoryChange }) {

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

        const routingControl = L.Routing.control({
            waypoints: waypoints.map(wp => L.latLng(wp.lat, wp.lng)),
            routeWhileDragging: true,
            showAlternatives: true,
            altLineOptions: {
                styles: [
                    {
                        color: 'red',
                    },
                    {
                        color: 'blue',
                    },
                    {
                        color: 'grey',
                    }
                ],
            },
        }).addTo(map);

        routingControl.on('routesfound', function (e) {
            console.log('routes found ', e.routes)                      // TODO: CHECK THIS CONSOLE.LOG
            const trajectory = e.routes[0].coordinates;

            // Pass the trajectory waypoints back to the form through the callback
            onTrajectoryChange(trajectory);
        });

        return () => map.removeControl(routingControl);
    }, [map, waypoints]);

    return null;
}

export default function ExperimentalMap({ waypoints, onTrajectoryChange }) {
    console.log(waypoints)
    return (
        <MapContainer center={[32.6443385, -16.9167589]} zoom={12} style={{ height: '500px', width: '100%' }}>
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Routing waypoints={waypoints} onTrajectoryChange={onTrajectoryChange}/>
        </MapContainer>
    );
}
