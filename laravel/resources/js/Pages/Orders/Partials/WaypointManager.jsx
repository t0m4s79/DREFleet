import { useState, useEffect, useContext } from 'react';
import { Autocomplete, TextField, Button, Grid, Typography, List, ListItem } from '@mui/material';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import ExperimentalMap from '@/Components/ExperimentalMap';
import { OrderContext } from '../OrderContext';

export default function WaypointManager({ kids, otherPlacesList, updateSummary }) {
    const { 
        waypoints,
        places,
        updateWaypoints,
        updatePlaces,
        updateTrajectory,
    } = useContext(OrderContext);

    const [selectedKid, setSelectedKid] = useState(null);
    const [selectedKidPlace, setSelectedKidPlace] = useState(null);
    const [selectedOtherPlace, setSelectedOtherPlace] = useState(null);

    console.log('waypoint manager receives waypoints', waypoints)
    console.log('waypoint manager receives places', places)

    useEffect(() => {
        if (waypoints.length > 0) {
            const newPlaces = waypoints.map((waypoint, index) => {
                const existingPlace = places.find(p => p.place_id === waypoint.place_id) || {};
    
                return {
                    place_id: waypoint.place_id,
                    kid_id: waypoint.kid_id || null, // Ensure kid_id is included even if null
                    stop_number: index + 1,
                    distance: existingPlace.distance || 0, // Keep existing metric data if available
                    time: existingPlace.time || 0,         // Keep existing metric data if available
                };
            });
    
            // Update the places array in context with the new structure
            updatePlaces(newPlaces);
            updateTrajectory(); // Update trajectory after places are set
        }
    }, [waypoints, updateTrajectory]);
    
    
    const addWaypoint = (waypoint, placeId) => {
        const newWaypoints = [...waypoints, waypoint];
        updateWaypoints(newWaypoints);
        updatePlaces([...places, { 
            place_id: placeId, 
            kid_id: waypoint.kid_id,
            stop_number: places.length + 1, 
            label: waypoint.label, 
            lat: waypoint.lat, 
            lng: waypoint.lng,
            distance: 0, time: 0 }]);
    };

    const updateMetricData = (newMetrics) => {
        console.log('metric data being updated')
        const updatedPlaces = places.map((place, index) => ({
            ...place,
            distance: newMetrics[index]?.distance || place.distance,
            time: newMetrics[index]?.time || place.time,
        }));
        updatePlaces([...updatedPlaces]);
    };

    const addKid = () => {
        if (selectedKid && selectedKidPlace) {
            addWaypoint({
                kid_id: selectedKid.id,
                place_id: selectedKidPlace.id,
                label: `#${selectedKidPlace.id} - ${selectedKidPlace.address} ###${selectedKid.id} - ${selectedKid.name}`,
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
                place_id: selectedOtherPlace.place_id,
                label: selectedOtherPlace.label,
                lat: selectedOtherPlace.lat,
                lng: selectedOtherPlace.lng,
            }, selectedOtherPlace.place_id);
            setSelectedOtherPlace(null);
        }
    };

    const removeLastWaypoint = () => {
        if (waypoints.length) {
            const newWaypoints = waypoints.slice(0, -1);
            const newPlaces = places.slice(0, -1);
            updateWaypoints(newWaypoints);
            updatePlaces(newPlaces);
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

        updateWaypoints(reorderedWaypoints);
        updatePlaces(updatedPlacesWithStopNumber);
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
                                        <Draggable key={waypoint.place_id} draggableId={waypoint.place_id.toString()} index={index}>
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
