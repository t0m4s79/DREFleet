import { useState, useEffect } from 'react';
import { Autocomplete, TextField, Button, Grid, Typography, List, ListItem } from '@mui/material';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import ExperimentalMap from '@/Components/ExperimentalMap';

export default function WaypointManager({ kids, otherPlacesList, onUpdateWaypoints, waypointsList, updateTrajectory, updateSummary, updateWaypointData }) {
    const [waypoints, setWaypoints] = useState([]);
    const [places, setPlaces] = useState([]);
    const [selectedKid, setSelectedKid] = useState(null);
    const [selectedKidPlace, setSelectedKidPlace] = useState(null);
    const [selectedOtherPlace, setSelectedOtherPlace] = useState(null);

    useEffect(() => {
        if (waypointsList?.length > 0) {
            setWaypoints(waypointsList);
            setPlaces(waypointsList.map((waypoint, index) => ({
                place_id: waypoint.id,
                kid_id: waypoint.kid?.id || null,
                stop_number: index + 1,
                // Initialize metric data if necessary
                distance: 0, // or whatever default makes sense
                time: 0,
            })));
        }
    }, [waypointsList]);

    const updateWaypointsAndPlaces = (newWaypoints, newPlaces) => {
        setWaypoints(newWaypoints);
        setPlaces(newPlaces);
        onUpdateWaypoints(newWaypoints, newPlaces);
    };

    const addWaypoint = (waypoint, placeId) => {
        const newWaypoints = [...waypoints, waypoint];
        const newPlaces = [...places, { place_id: placeId, stop_number: places.length + 1, distance: 0, duration: 0 }];
        updateWaypointsAndPlaces(newWaypoints, newPlaces);
    };

    const updateMetricData = (newMetrics) => {
        const updatedPlaces = places.map((place, index) => ({
            ...place,
            distance: newMetrics[index]?.distance || place.distance, // Update distance
            time: newMetrics[index]?.time || place.time, // Update duration
        }));
        setPlaces(updatedPlaces);
        onUpdateWaypoints(waypoints, updatedPlaces); // Ensure the updated places are sent back
    };


    const addKid = () => {
        if (selectedKid && selectedKidPlace) {
            addWaypoint({
                kid: selectedKid,
                id: selectedKidPlace.id,
                label: `#${selectedKidPlace.id} - ${selectedKidPlace.address}`,
                lat: selectedKidPlace.coordinates.coordinates[1],
                lng: selectedKidPlace.coordinates.coordinates[0],
            }, selectedKidPlace.id);
            setSelectedKid(null);
            setSelectedKidPlace(null);
        }
    };

    const addOtherPlace = () => {
        if (selectedOtherPlace) {
            addWaypoint({
                id: selectedOtherPlace.id,
                label: selectedOtherPlace.label,
                lat: selectedOtherPlace.lat,
                lng: selectedOtherPlace.lng,
            }, selectedOtherPlace.id);
            setSelectedOtherPlace(null);
        }
    };

    const removeLastWaypoint = () => {
        if (waypoints.length) {
            updateWaypointsAndPlaces(waypoints.slice(0, -1), places.slice(0, -1));
        }
    };

    const onDragEnd = (result) => {
        if (!result.destination) return;

        const reorderedWaypoints = Array.from(waypoints);
        const reorderedPlaces = Array.from(places);
        const [removedWaypoint] = reorderedWaypoints.splice(result.source.index, 1);
        const [removedPlace] = reorderedPlaces.splice(result.source.index, 1);
        reorderedWaypoints.splice(result.destination.index, 0, removedWaypoint);
        reorderedPlaces.splice(result.destination.index, 0, removedPlace);

        const updatedPlacesWithStopNumber = reorderedPlaces.map((place, index) => ({
            ...place,
            stop_number: index + 1,
        }));

        updateWaypointsAndPlaces(reorderedWaypoints, updatedPlacesWithStopNumber);
    };

    return (
        <Grid container spacing={3}>
            <Grid item xs={12} md={6}>
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
                                                        {waypoint.kid ? `${waypoint.kid.name} - ${waypoint.label}` : waypoint.label}
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

                <Grid item xs={12}>
                    <Autocomplete
                        options={kids}
                        getOptionLabel={(kid) => `#${kid.id} - ${kid.name}`}
                        onChange={(event, kid) => setSelectedKid(kid)}
                        renderInput={(params) => <TextField {...params} label="Criança" />}
                    />
                    {selectedKid && (
                        <Autocomplete
                            options={selectedKid.places || []}
                            getOptionLabel={(place) => place.address}
                            onChange={(event, place) => setSelectedKidPlace(place)}
                            renderInput={(params) => <TextField {...params} label="Morada da Criança" />}
                        />
                    )}
                </Grid>

                <Grid item xs={12}>
                    <Autocomplete
                        options={otherPlacesList}
                        getOptionLabel={(place) => place.label}
                        onChange={(event, place) => setSelectedOtherPlace(place)}
                        renderInput={(params) => <TextField {...params} label="Outro Local" />}
                    />
                </Grid>

                <Grid item xs={12}>
                    <Button onClick={addKid} disabled={!selectedKid || !selectedKidPlace}>
                        Add Kid
                    </Button>
                    <Button onClick={addOtherPlace} disabled={!selectedOtherPlace}>
                        Add Other Place
                    </Button>
                    <Button onClick={removeLastWaypoint} disabled={!waypoints.length}>
                        Remove Last Waypoint
                    </Button>
                </Grid>
            </Grid>

            <Grid item xs={12} md={6}>
                <ExperimentalMap 
                    waypoints={waypoints} 
                    onTrajectoryChange={updateTrajectory} 
                    updateSummary={updateSummary} 
                    updateWaypointData={updateMetricData}
                />
            </Grid>
        </Grid>
    );
}