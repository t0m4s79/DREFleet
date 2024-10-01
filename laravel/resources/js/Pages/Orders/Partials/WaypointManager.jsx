import { useState } from 'react';
import { Autocomplete, TextField, Button, Grid, Typography, List, ListItem } from '@mui/material';

export default function WaypointManager({ kids, otherPlacesList, onUpdateWaypoints }) {
	const [selectedKid, setSelectedKid] = useState(null);
	const [selectedKidPlace, setSelectedKidPlace] = useState(null);
	const [selectedOtherPlace, setSelectedOtherPlace] = useState(null);
	const [waypoints, setWaypoints] = useState([]);
	const [places, setPlaces] = useState([]);

	const handleKidChange = (kid) => {
		setSelectedKid(kid);
	};

	const handlePlaceChange = (place) => {
		if (selectedKid && place) {
		setSelectedKidPlace(place);
		}
	};

	const handleOtherPlaceChange = (place) => {
		setSelectedOtherPlace(place);
	};

	const addKid = () => {
		if (selectedKid && selectedKidPlace) {
		const newWaypoint = {
			kid: selectedKid,
			id: selectedKidPlace.id,
			label: `#${selectedKidPlace.id} - ${selectedKidPlace.address}`,
			lat: selectedKidPlace.coordinates.coordinates[1],
			lng: selectedKidPlace.coordinates.coordinates[0],
		};
		setWaypoints((prev) => [...prev, newWaypoint]);
		setPlaces((prev) => [...prev, { place_id: selectedKidPlace.id, kid_id: selectedKid.id }]);
		onUpdateWaypoints([...waypoints, newWaypoint], [...places, { place_id: selectedKidPlace.id, kid_id: selectedKid.id }]);
		setSelectedKid(null);
		setSelectedKidPlace(null);
		}
	};

	const addOtherPlace = () => {
		if (selectedOtherPlace) {
		const newWaypoint = {
			id: selectedOtherPlace.id,
			label: selectedOtherPlace.label,
			lat: selectedOtherPlace.lat,
			lng: selectedOtherPlace.lng,
		};
		setWaypoints((prev) => [...prev, newWaypoint]);
		setPlaces((prev) => [...prev, { place_id: selectedOtherPlace.id }]);
		onUpdateWaypoints([...waypoints, newWaypoint], [...places, { place_id: selectedOtherPlace.id }]);
		setSelectedOtherPlace(null);
		}
	};

	const removeLastWaypoint = () => {
		const updatedWaypoints = waypoints.slice(0, -1);
		const updatedPlaces = places.slice(0, -1);
		setWaypoints(updatedWaypoints);
		setPlaces(updatedPlaces);
		onUpdateWaypoints(updatedWaypoints, updatedPlaces);
	};

	return (
		<Grid container spacing={3}>
		<Grid item xs={12}>
			<Typography>Pontos de Paragem:</Typography>
			<List style={{ minHeight: '200px', maxHeight: '500px' , overflowY: 'scroll'}}>
			{waypoints.map((waypoint, index) => (
				<ListItem key={index}>
				<Typography>{waypoint.kid ? `${waypoint.kid.name} - ${waypoint.label}` : waypoint.label}</Typography>
				</ListItem>
			))}
			</List>
		</Grid>

		<Grid item xs={12}>
			<Autocomplete
			options={kids}
			getOptionLabel={(kid) => kid.name}
			onChange={(event, kid) => handleKidChange(kid)}
			renderInput={(params) => <TextField {...params} label="Criança" />}
			/>
			{selectedKid && (
			<Autocomplete
				options={selectedKid.places || []}
				getOptionLabel={(place) => place.address}
				onChange={(event, place) => handlePlaceChange(place)}
				renderInput={(params) => <TextField {...params} label="Morada da Criança" />}
			/>
			)}
		</Grid>

		<Grid item xs={12}>
			<Autocomplete
			options={otherPlacesList}
			getOptionLabel={(place) => place.label}
			onChange={(event, place) => handleOtherPlaceChange(place)}
			renderInput={(params) => <TextField {...params} label="Outro Local" />}
			/>
		</Grid>

		<Grid item xs={12}>
			<Button onClick={addKid} disabled={!selectedKid || !selectedKidPlace}>Add Kid</Button>
			<Button onClick={addOtherPlace} disabled={!selectedOtherPlace}>Add Other Place</Button>
			<Button onClick={removeLastWaypoint} disabled={waypoints.length === 0}>Remove Last Waypoint</Button>
		</Grid>
		</Grid>
	);
	}
