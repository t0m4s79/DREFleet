import { useState, useEffect } from 'react';
import { Autocomplete, TextField, Button, Grid, Typography, List, ListItem } from '@mui/material';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

export default function WaypointManager({ kids, otherPlacesList, onUpdateWaypoints, waypointsList }) {
    const [waypoints, setWaypoints] = useState([]);
    const [places, setPlaces] = useState([]);
    const [selectedKid, setSelectedKid] = useState(null);
    const [selectedKidPlace, setSelectedKidPlace] = useState(null);
    const [selectedOtherPlace, setSelectedOtherPlace] = useState(null);

    useEffect(() => {
        if (waypointsList && waypointsList.length > 0) {
            setWaypoints(waypointsList);
            const initialPlaces = waypointsList.map((waypoint, index) => ({
                place_id: waypoint.id,
                kid_id: waypoint.kid ? waypoint.kid.id : null,
                stop_number: index + 1, // Initialize the stop_number
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
            const updatedPlaces = [...places, { place_id: selectedKidPlace.id, kid_id: selectedKid.id, stop_number: places.length + 1 }];
            setWaypoints(updatedWaypoints);
            setPlaces(updatedPlaces);
            onUpdateWaypoints(updatedWaypoints, updatedPlaces);
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
            const updatedWaypoints = [...waypoints, newWaypoint];
            const updatedPlaces = [...places, { place_id: selectedOtherPlace.id, stop_number: places.length + 1 }];
            setWaypoints(updatedWaypoints);
            setPlaces(updatedPlaces);
            onUpdateWaypoints(updatedWaypoints, updatedPlaces);
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

    const onDragEnd = (result) => {
        if (!result.destination) return;

        const reorderedWaypoints = Array.from(waypoints);
        const reorderedPlaces = Array.from(places);
        
        // Reorder waypoints and places
        const [removedWaypoint] = reorderedWaypoints.splice(result.source.index, 1);
        const [removedPlace] = reorderedPlaces.splice(result.source.index, 1);
        reorderedWaypoints.splice(result.destination.index, 0, removedWaypoint);
        reorderedPlaces.splice(result.destination.index, 0, removedPlace);

        // Update stop_number based on new order
        const updatedPlacesWithStopNumber = reorderedPlaces.map((place, index) => ({
            ...place,
            stop_number: index + 1, // Set stop_number based on the new index
        }));

        setWaypoints(reorderedWaypoints);
        setPlaces(updatedPlacesWithStopNumber);
        onUpdateWaypoints(reorderedWaypoints, updatedPlacesWithStopNumber);
    };

    return (
        <Grid container spacing={3}>
            <Grid item xs={12}>
                <Typography>Pontos de Paragem:</Typography>
                <DragDropContext onDragEnd={onDragEnd}>
                    <Droppable droppableId="waypoints-list">
                        {(provided) => (
                            <List
                                style={{ minHeight: '200px', maxHeight: '500px', overflowY: 'scroll' }}
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                            >
                                {waypoints.map((waypoint, index) => (
                                    <Draggable key={index} draggableId={index.toString()} index={index}>
                                        {(provided) => (
                                            <ListItem
                                                ref={provided.innerRef}
                                                {...provided.draggableProps}
                                                {...provided.dragHandleProps}
                                            >
                                                <Typography>
                                                    {waypoint.kid
                                                        ? `${waypoint.kid.name} - ${waypoint.label}`
                                                        : waypoint.label}
                                                </Typography>
                                            </ListItem>
                                        )}
                                    </Draggable>
                                ))}
                                {provided.placeholder}
                            </List>
                        )}
                    </Droppable>
                </DragDropContext>
            </Grid>

            {/* Kid Selection */}
            <Grid item xs={12}>
                <Autocomplete
                    options={kids}
                    getOptionLabel={(kid) => `#${kid.id} - ${kid.name}`}
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
