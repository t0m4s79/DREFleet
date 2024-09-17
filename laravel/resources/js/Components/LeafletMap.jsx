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

function Routing() {
	const map = useMap();
	const routingControlRef = useRef(null);
	const geocoderRef = useRef(null);
  
	useEffect(() => {
	  // Only add the routing control once
	  if (!routingControlRef.current) {
		routingControlRef.current = L.Routing.control({
		//   waypoints: [
		// 	L.latLng(32.6443385, -16.9167589),
		// 	L.latLng(32.6443385, -16.8167589),
		// 	L.latLng(32.7443385, -16.9167589),
		//   ],
		geocoder: L.Control.Geocoder.nominatim()		//Allows geocoding
	}).addTo(map);
	  }
  
	  // Only add the geocoder once
	  if (!geocoderRef.current) {
		geocoderRef.current = L.Control.geocoder({
		  defaultMarkGeocode: false, // Prevent default marker
		})
		  .on('markgeocode', function (e) {
			const latlng = e.geocode.center;
			L.marker(latlng).addTo(map); // Add marker at geocoded location
			map.setView(latlng, map.getZoom()); // Center the map on geocoded location
		  })
		  .addTo(map);
	  }
  
	  // Clean up both controls when component unmounts
	  return () => {
		if (routingControlRef.current) {
		  map.removeControl(routingControlRef.current);
		  routingControlRef.current = null;
		}
		if (geocoderRef.current) {
		  map.removeControl(geocoderRef.current);
		  geocoderRef.current = null;
		}
	  };
	}, [map]);
  
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

export default function LeafletMap() {			//MAP
    return (
    <MapContainer center={[32.6443385, -16.9167589]} zoom={12} style={{ height: '500px', width: '90%', margin: 'auto' }}>
        <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {/* <Marker position={[32.6443385, -16.9167589]}>
        <Popup>Start Point</Popup>
        </Marker> */}
        <Routing/>
        {/* {<DraggableMarker />} */}
        {/* <LocationMarker /> */}
    </MapContainer>
    );
}
