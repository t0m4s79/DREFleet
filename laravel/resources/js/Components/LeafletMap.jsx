import React, { useEffect, useState, useRef, useMemo, useCallback } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';
import 'leaflet-control-geocoder/dist/Control.Geocoder.css';
import 'leaflet-control-geocoder';

const center = {
    lat: 32.6443385,
    lng: -16.9167589,
}

function Routing({ routing, onLocationSelect }) {
    const map = useMap();
    const routingControlRef = useRef(null);
    const geocoderRef = useRef(null);

    useEffect(() => {
        // Add the geocoder control (it will always be present)
        if (!geocoderRef.current) {
            geocoderRef.current = L.Control.geocoder({
                defaultMarkGeocode: false
            })
                .on('markgeocode', function (e) {
                    const latlng = e.geocode.center;
                    var marker = new L.marker(latlng, {draggable:'true'}).addTo(map);
					marker.on('dragend', function(event){
						var position = marker.getLatLng();
						marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
						map.panTo(new L.LatLng(position.lat, position.lng))
						console.log('latlng', marker.getLatLng())
						onLocationSelect(marker.getLatLng().lat,marker.getLatLng().lng)
					});
					map.addLayer(marker);
					onLocationSelect(marker.getLatLng().lat,marker.getLatLng().lng)
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
                geocoder: L.Control.Geocoder.nominatim(),
                language: 'pt',
            }).addTo(map);
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
    }, [map, routing]); // Re-run effect when map or routing changes

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

function DraggableMarker() {						//Function to create dragable map
    const [draggable, setDraggable] = useState(false)
    const [position, setPosition] = useState(center)
    const markerRef = useRef(null)
    const eventHandlers = useMemo(
      () => ({
        dragend() {
          const marker = markerRef.current
          if (marker != null) {
            setPosition(marker.getLatLng())
          }
        },
      }),
      [],
    )
    const toggleDraggable = useCallback(() => {
      setDraggable((d) => !d)
    }, [])
  
    return (
      <Marker
        draggable={draggable}
        eventHandlers={eventHandlers}
        position={position}
        ref={markerRef}>
        <Popup minWidth={90}>
          <span onClick={toggleDraggable}>
            {draggable
              ? 'Marker is draggable'
              : 'Click here to make marker draggable'}
          </span>
        </Popup>
      </Marker>
    )
}

export default function LeafletMap({ routing, onLocationSelect }) {  // routing -> activate (true) routing or not (false)
    return (
        <MapContainer center={[32.6443385, -16.9167589]} zoom={12} style={{ height: '500px', width: '100%', margin: 'auto', zIndex: '5' }}>
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            {/* <Marker position={[32.6443385, -16.9167589]}>
                <Popup>Start Point</Popup>
            </Marker> */}
            {<Routing routing={routing} onLocationSelect={onLocationSelect}/>}
            {/* {<DraggableMarker />} */}
            {/* {<LocationMarker />} */}
        </MapContainer>
    );
}
