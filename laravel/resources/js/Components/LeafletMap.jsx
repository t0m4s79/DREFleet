import React, { useEffect, useState, useRef, useMemo, useCallback } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents, Polygon, Polyline } from 'react-leaflet';
import L, { latLng } from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';
import 'leaflet-control-geocoder/dist/Control.Geocoder.css';
import 'leaflet-control-geocoder';

const center = {
    lat: 32.6443385,
    lng: -16.9167589,
}

const boundsSouthWestCorner = [32.269181, -17.735033];
const boundsNorthEastCorner = [33.350247, -15.861279];

function Routing({ routing, onLocationSelect, onTrajectoryChange }) {
    const map = useMap();
    const routingControlRef = useRef(null);
    const geocoderRef = useRef(null);

    useEffect(() => {
		// Define the bounds (southwest and northeast corners)
        const bounds = L.latLngBounds(
            boundsSouthWestCorner, // Southwest corner
            boundsNorthEastCorner  // Northeast corner
        );

        // Set max bounds to restrict map area
        map.setMaxBounds(bounds);
        map.on('drag', function() {
            map.panInsideBounds(bounds, { animate: false });
        });

        // Add the geocoder control (it will always be present)
        if (!geocoderRef.current) {
            geocoderRef.current = L.Control.geocoder({
                defaultMarkGeocode: false,
				geocodingQueryParams: {
                    countrycodes: 'pt', // Still restrict by country if necessary
                    bounded: 1,
                    viewbox: `${boundsSouthWestCorner[1]},${boundsSouthWestCorner[0]},${boundsNorthEastCorner[1]},${boundsNorthEastCorner[0]}`
                }
            })
            .on('markgeocode', function (e) {
                    const latlng = e.geocode.center;
                    var marker = new L.marker(latlng, {draggable:'true'}).addTo(map);
					marker.on('dragend', function(event){
						var position = marker.getLatLng();
						marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
						map.panTo(new L.LatLng(position.lat, position.lng))
						onLocationSelect(marker.getLatLng().lat,marker.getLatLng().lng)
					});
					map.addLayer(marker);
					onLocationSelect(marker.getLatLng().lat,marker.getLatLng().lng);
                    map.setView(latlng, map.getZoom());
                })
                .addTo(map);
        }

        // Conditionally add the routing control only when routing is true
        if (routing && !routingControlRef.current) {
            routingControlRef.current = L.Routing.control({
                router: L.Routing.osrmv1({
                    serviceUrl: `https://router.project-osrm.org/route/v1`, //TODO: route instructions to Portuguese
                }),
                geocoder: L.Control.Geocoder.nominatim({
					geocodingQueryParams: {
                        countrycodes: 'pt', // Still restrict by country
                        bounded: 1,
                        viewbox: `${boundsSouthWestCorner[1]},${boundsSouthWestCorner[0]},${boundsNorthEastCorner[1]},${boundsNorthEastCorner[0]}`
                    }
				}),
                language: 'pt',
            }).addTo(map);

			routingControlRef.current.on('routesfound', function (e) {
                const waypoints = e.routes[0].waypoints.map((wp) => ({
                    lat: wp.latLng.lat,
                    lng: wp.latLng.lng
                }));

                // Pass the trajectory waypoints back to the form through the callback
                onTrajectoryChange(waypoints);
            });
        }

        // Cleanup on component unmount
        return () => {
            // Remove routing control if it was added
            if (routingControlRef.current) {
                map.removeControl(routingControlRef.current);
                routingControlRef.current = null;
            }
            // Remove geocoder control if it was added
            if (geocoderRef.current) {
                map.removeControl(geocoderRef.current);
                geocoderRef.current = null;
            }
        };
    }, [map, routing, onTrajectoryChange]); // Re-run effect when map or routing changes

    return null;
}


function LocationMarker() {							//Function to get user location on map click
    const [position, setPosition] = useState(null)
    const map = useMapEvents({
		click() {
			map.locate()
		},
		locationfound(e) {
			setPosition(e.latlng)
			map.flyTo(e.latlng, map.getZoom())
		},
    })
  
    return position === null ? null : (
		<Marker position={position}>
			<Popup>You are here</Popup>
		</Marker>
    )
}

function EditMarker({ initialPos, onPositionChange }) {
    const [draggable, setDraggable] = useState(false);
    const [position, setPosition] = useState(initialPos);
    const markerRef = useRef(null);
    const map = useMap(); // Get the map instance
    const geocoderRef = useRef(null); // To ensure geocoder is only added once

    const eventHandlers = useMemo(
        () => ({
            dragend() {
                const marker = markerRef.current;
                if (marker != null) {
                    setPosition(marker.getLatLng());
                    // Update the form coordinates with the new marker position
                    onPositionChange(marker.getLatLng().lat, marker.getLatLng().lng);
                }
            },
        }),
        [onPositionChange]
    );

    const toggleDraggable = useCallback(() => {
        setDraggable((d) => !d);
    }, []);

    useEffect(() => {

        // Define the bounds (southwest and northeast corners)
        const bounds = L.latLngBounds(
            boundsSouthWestCorner, // Southwest corner
            boundsNorthEastCorner  // Northeast corner
        );

        // Set max bounds to restrict map area
        map.setMaxBounds(bounds);
        map.on('drag', function() {
            map.panInsideBounds(bounds, { animate: false });
        });
        map.flyTo(position);
        // Add geocoder search bar only for the edit page
        if (!geocoderRef.current) {
            geocoderRef.current = L.Control.geocoder({
                defaultMarkGeocode: false,
                geocoder: L.Control.Geocoder.nominatim({
					geocodingQueryParams: {
                        countrycodes: 'pt', // Still restrict by country
                        bounded: 1,
                        viewbox: `${boundsSouthWestCorner[1]},${boundsSouthWestCorner[0]},${boundsNorthEastCorner[1]},${boundsNorthEastCorner[0]}`
                    }
				}),
            })
                .once('markgeocode', function (e) {
                    const latlng = e.geocode.center;
                    setPosition(latlng);
                    //map.setView(latlng, map.getZoom());   // If client decides to change back
                    map.flyTo(latlng, map.getZoom());
                    // Update the form coordinates with the geocoder result
                    onPositionChange(latlng.lat, latlng.lng);
                })
                .addTo(map);
        }

        // Cleanup geocoder on unmount
        return () => {
            if (geocoderRef.current) {
                map.removeControl(geocoderRef.current);
                geocoderRef.current = null;
            }
        };
    }, [map, onPositionChange]);

    return (
        <Marker
            draggable={draggable}
            eventHandlers={eventHandlers}
            position={position}
            ref={markerRef}
        >
            <Popup minWidth={90}>
                <span onClick={toggleDraggable}>
                    {draggable
                        ? 'Pode arrastar o marcador, clique novamente para fixá-lo'
                        : 'Clique aqui para arrastar o marcador'}
                </span>
            </Popup>
        </Marker>
    );
}

export default function LeafletMap({ routing, onLocationSelect, onTrajectoryChange, initialPosition, edditing, polygonCoordinates, polygonColor, trajectory}) {  // routing -> activate (true) routing or not (false)
    
    let polyCoords
    if(polygonCoordinates) {
        polyCoords = polygonCoordinates.coordinates[0].map((coords)=>{
            return latLng({
                lat: coords[1],
                lng: coords[0]
            })
        })
        console.log(polyCoords)
    }
    
    return (
        <MapContainer center={[32.6443385, -16.9167589]} zoom={12} style={{ height: '500px', width: '100%', margin: 'auto', zIndex: '5' }}>
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            {routing && <Routing routing={routing} onLocationSelect={onLocationSelect} onTrajectoryChange={onTrajectoryChange} />}

            {edditing && <EditMarker initialPos={initialPosition} onPositionChange={onLocationSelect} />}

            {polygonCoordinates && (
                <Polygon 
                    positions={polyCoords}
                    pathOptions={{ color: polygonColor }}
                />
            )}

            {trajectory && (
                <Polyline positions={trajectory} color="red" weight={2} />
            )}
            
        </MapContainer>
    );
}
