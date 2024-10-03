import { useState, useEffect } from 'react';
import { Autocomplete, TextField, Button, Grid, Typography, List, ListItem } from '@mui/material';

export default function WaypointManager({ kids, otherPlacesList, onUpdateWaypoints, waypointsList }) {
	// Initialize state from the waypointsList prop
	const [waypoints, setWaypoints] = useState([]);
	const [places, setPlaces] = useState([]);
	const [selectedKid, setSelectedKid] = useState(null);
	const [selectedKidPlace, setSelectedKidPlace] = useState(null);
	const [selectedOtherPlace, setSelectedOtherPlace] = useState(null);

	// Sync waypointsList from props with local state on mount and when waypointsList changes
	useEffect(() => {
		if (waypointsList && waypointsList.length > 0) {
			setWaypoints(waypointsList);
			const initialPlaces = waypointsList.map((waypoint) => ({
				place_id: waypoint.id,
				kid_id: waypoint.kid ? waypoint.kid.id : null,
			}));
			setPlaces(initialPlaces);
		}
	}, [waypointsList]);

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

	// Add selected kid and their place to waypoints
	const addKid = () => {
		if (selectedKid && selectedKidPlace) {
			const newWaypoint = {
				kid: selectedKid,
				id: selectedKidPlace.id,
				label: `#${selectedKidPlace.id} - ${selectedKidPlace.address}`,
				lat: selectedKidPlace.coordinates.coordinates[1],
				lng: selectedKidPlace.coordinates.coordinates[0],
			};
			const updatedWaypoints = [...waypoints, newWaypoint];
			const updatedPlaces = [...places, { place_id: selectedKidPlace.id, kid_id: selectedKid.id }];
			setWaypoints(updatedWaypoints);
			setPlaces(updatedPlaces);
			onUpdateWaypoints(updatedWaypoints, updatedPlaces);
			setSelectedKid(null);
			setSelectedKidPlace(null);
		}
	};

	// Add other place to waypoints
	const addOtherPlace = () => {
		if (selectedOtherPlace) {
			const newWaypoint = {
				id: selectedOtherPlace.id,
				label: selectedOtherPlace.label,
				lat: selectedOtherPlace.lat,
				lng: selectedOtherPlace.lng,
			};
			const updatedWaypoints = [...waypoints, newWaypoint];
			const updatedPlaces = [...places, { place_id: selectedOtherPlace.id }];
			setWaypoints(updatedWaypoints);
			setPlaces(updatedPlaces);
			onUpdateWaypoints(updatedWaypoints, updatedPlaces);
			setSelectedOtherPlace(null);
		}
	};

	// Remove the last waypoint
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
				<List style={{ minHeight: '200px', maxHeight: '500px', overflowY: 'scroll' }}>
					{waypoints.map((waypoint, index) => (
						<ListItem key={index}>
							<Typography>{waypoint.kid ? `${waypoint.kid.name} - ${waypoint.label}` : waypoint.label}</Typography>
						</ListItem>
					))}
				</List>
			</Grid>

			{/* Kid Selection */}
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

			{/* Other Places Selection */}
			<Grid item xs={12}>
				<Autocomplete
					options={otherPlacesList}
					getOptionLabel={(place) => place.label}
					onChange={(event, place) => handleOtherPlaceChange(place)}
					renderInput={(params) => <TextField {...params} label="Outro Local" />}
				/>
			</Grid>

			{/* Buttons for adding and removing waypoints */}
			<Grid item xs={12}>
				<Button onClick={addKid} disabled={!selectedKid || !selectedKidPlace}>
					Add Kid
				</Button>
				<Button onClick={addOtherPlace} disabled={!selectedOtherPlace}>
					Add Other Place
				</Button>
				<Button onClick={removeLastWaypoint} disabled={waypoints.length === 0}>
					Remove Last Waypoint
				</Button>
			</Grid>
		</Grid>
	);
}