import { Head, Link, useForm } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Box, Autocomplete, Typography, List, ListItem } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useCallback, useState } from 'react';
import ExperimentalMap from '@/Components/ExperimentalMap';
import WaypointManager from './Partials/WaypointManager';

export default function NewOrder({auth, drivers, vehicles, technicians, managers, kids, otherPlaces, orderRoutes}) {

    //console.log('drivers', drivers);
    // console.log(vehicles);
    //console.log('technicians', technicians);
    // console.log(managers);
    console.log('kids', kids)
    console.log('otherplaces', otherPlaces)

    //const [waypoints, setWaypoints] = useState([]);
    //const [newWaypoint, setNewWaypoint] = useState({label: '', lat: '', lng: '' });
    const [selectedRouteType, setSelectedRouteType]= useState('');
    const [selectedRouteID, setSelectedRouteID] =useState('');
    const [trajectory, setTrajectory] = useState([]);
    const [selectedKid, setSelectedKid] = useState({});
    const [selectedKidPlace, setSelectedKidPlace] = useState({});
    const [selectedOtherPlace, setSelectedOtherPlace] = useState({});
    const [waypoints, setWaypoints] = useState([]);
    const [places, setPlaces] = useState([]);

    // Deconstruct places to change label display
    const otherPlacesList = otherPlaces.map((place) => ({
        id: place.id,
        label: `#${place.id} - ${place.address}`,
        lat: place.coordinates.coordinates[1],
        lng: place.coordinates.coordinates[0],
    }));

    const driversList = drivers.map((driver) => {
        return {value: driver.user_id, label: `#${driver.user_id} - ${driver.name}`}
    })

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    const techniciansList = technicians.map((technician) => {
        return {value: technician.id, label: `#${technician.id} - ${technician.name}`}
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const { data, setData, post, errors, processing} = useForm({
        expected_begin_date: '',
        expected_end_date: '',
        order_type: '',
        vehicle_id: '',
        driver_id: '',
        technician_id: '',
        trajectory: [],
        order_route_id: '',
        places: [],         //waypoints
    })

    // const removeLastWaypoint = () => {
    //     if (waypoints.length > 1) {
    //         setWaypoints(waypoints.slice(0, -1));
    //     }
    // };

    const handleRouteChange =(route) => {
        setSelectedRouteID(route)
        setData('order_route_id',route)
    }

    const handleRouteType = (type) => {
        setSelectedRouteType(type)
        setData('order_type', type)
    }

    // const handleKidChange = (kid) => {
    //     setSelectedKid(kid);
    // };
    
    // const handlePlaceChange = (place) => {
    //     // Update the place for the last selected kid
    //     if (selectedKid && place) {
        
    //         // Update waypoints
    //         setSelectedKidPlace(place);
    //     }
    // };
    
    // const handleOtherPlaceChange = (place) => {
    //     console.log('handler place', place)
    //     // Add selected other place to waypoints
    //     if (place) {
    //         setSelectedOtherPlace(place);
    //     }
    // };
    
    // const addKid = () => {
    //     setWaypoints((prevWaypoints) => [...prevWaypoints, {
    //         kid: selectedKid,
    //         id: selectedKidPlace.id,
    //         label: `#${selectedKidPlace.id} - ${selectedKidPlace.address}`,
    //         lat: selectedKidPlace.coordinates.coordinates[1],
    //         lng: selectedKidPlace.coordinates.coordinates[0],
    //     }])
    //     setPlaces([...places, {place_id: selectedKidPlace.id, kid_id: selectedKid.id}])
    //     setSelectedKid("")
    // };
    
    // const addOtherPlace = () => {
    //     setWaypoints((prevWaypoints) => [...prevWaypoints, {id: selectedOtherPlace.id, label: selectedOtherPlace.label, lat: selectedOtherPlace.lat, lng: selectedOtherPlace.lng}])
    //     setPlaces([...places, {place_id: selectedOtherPlace.id}])
    // };
    
    // const removeLastWaypoint = () => {
    //     // Remove the last waypoint
    //     const updatedWaypoints = [...waypoints];
    //     updatedWaypoints.pop();
    //     setWaypoints(updatedWaypoints);
    // };     

    // const clearSelection = () => {
    //     setWaypoints([]);
    //     setSelectedKid(null);
    //     setTrajectory([]);
    // }

    const updateWaypoints = (newWaypoints, newPlaces) => {
        setWaypoints(newWaypoints);
        setPlaces(newPlaces);
        setData('places', newPlaces);
    };

    const updateTrajectory= (newTraj) => {
        console.log(newTraj)
        setTrajectory(newTraj)
        setData('trajectory', JSON.stringify(newTraj))
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        console.log('data', data);
        post(route('orders.create'));
    };

    console.log('waypoints', waypoints)
    console.log('trajectory', trajectory)
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Pedido</h2>}
        >

            <Head title="Novo Pedido" />
        
            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>
                            <div className='my-6'>
                                {/* <LeafletMap routing={true} onTrajectoryChange={(trajectory) => document.getElementById('trajectory').value = JSON.stringify(trajectory)} />     */}
                                {/* <LeafletMap routing={true} onTrajectoryChange={updateTrajectory} /> */}
                                
                                
                                    <form onSubmit={handleSubmit}>
                                        <input type="hidden" name="_token" value={csrfToken} />
                                        
                                        <input type="hidden" id="trajectory" name="trajectory"/>
                                        <Grid container spacing={3}>
                                            <Grid item xs={12} md={6}>

                                                <InputLabel sx={{ mb: 2 }}>Rota</InputLabel>
                                                <Autocomplete
                                                    options={orderRoutes}
                                                    getOptionLabel={(orderRoute) => orderRoute.name} 
                                                    value={orderRoutes.find(route => route.id === data.order_route_id) || null} // Match the selected object by id
                                                    onChange={(event, route) => handleRouteChange(route.id)}
                                                    //onChange={(event, value) => setData('order_route_id', value ? value.id : '')} // Set the id of the selected route
                                                    renderInput={(params) => <TextField {...params} label="Rota" />}
                                                    error={errors.order_route_id}
                                                    helperText={errors.order_route_id}
                                                    sx={{ mb: 2 }}
                                                />

                                                <InputLabel sx={{ mb: 2 }}>Tipo de Transporte</InputLabel>
                                                <Autocomplete
                                                    options={['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']}
                                                    value={data.order_type || null} // Bind the selected string
                                                    onChange={(event, value) => handleRouteType(value)}
                                                    //onChange={(event, value) => setData('order_type', value || '')} // Set the selected value (string)
                                                    renderInput={(params) => <TextField {...params} label="Tipo de Transporte" />}
                                                    error={errors.order_type}
                                                    helperText={errors.order_type}
                                                    sx={{ mb: 2 }}
                                                />

                                                {/* <InputLabel value={'Pontos de paragem'} />
                                                <Grid item xs={12} style={{ minHeight: '200px' , maxHeight: '400px' , overflowY: 'scroll'}}>
                                                    <List>
                                                        {waypoints.map((waypoint,index)=> (
                                                            <ListItem>
                                                                <Grid item xs={12} key={index}>
                                                                    <Typography key={index}>{waypoint.kid ? `${waypoint.kid.name} - ${waypoint.label}` : waypoint.label}</Typography>
                                                                </Grid>
                                                            </ListItem>
                                                        ))}
                                                    </List>
                                                </Grid>
                                                <Grid item xs={12}>
                                                    <InputLabel value={'Selecionar Criança e Morada respetiva'} />
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
                                                    <InputLabel value={'Selecionar Outro Local'} />
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
                                                    <Button onClick={removeLastWaypoint} disabled={waypoints.length <= 0}>Remove Last waypoint</Button>
                                                    <Button onClick={clearSelection}>Clear Route</Button>
                                                </Grid>*/}

                                                    <WaypointManager kids={kids} otherPlacesList={otherPlaces.map(place => ({
                                                            id: place.id,
                                                            label: `#${place.id} - ${place.address}`,
                                                            lat: place.coordinates.coordinates[1],
                                                            lng: place.coordinates.coordinates[0],
                                                        }))} onUpdateWaypoints={updateWaypoints}
                                                    />
                                                </Grid>

                                            <Grid item xs={12} md={6}>
                                                <ExperimentalMap waypoints={waypoints} onTrajectoryChange={updateTrajectory}/>
                                            </Grid>


                                        </Grid>
                    
                                        <Grid container spacing={3}>
                                            <Grid item xs={6}>
                                                <InputLabel htmlFor="planned_begin_date" value="Data e Hora de Início" />
                                                <TextField
                                                    //label="Data e Hora de Início"
                                                    id='planned_begin_date'
                                                    name='planned_begin_date'
                                                    type="datetime-local"
                                                    fullWidth
                                                    value={data.begin_date}
                                                    onChange={(e) => setData('expected_begin_date', e.target.value)}
                                                    error={errors.expected_begin_date}
                                                    helperText={errors.expected_begin_date}
                                                    sx={{ mb: 2 }}
                                                />
                                            </Grid>

                                            <Grid item xs={6}>
                                                <InputLabel htmlFor="planned_end_date" value="Data e Hora de Fim" />
                                                <TextField
                                                    // label="Data e Hora de Fim"
                                                    id='planned_end_date'
                                                    name='planned_end_date'
                                                    type="datetime-local"
                                                    fullWidth
                                                    value={data.end_date}
                                                    onChange={(e) => setData('expected_end_date', e.target.value)}
                                                    error={errors.expected_begin_date}
                                                    helperText={errors.expected_end_date}
                                                    sx={{ mb: 2 }}
                                                />
                                            </Grid>
                                        </Grid>
                                   
                                        <Grid item xs={12}>
                                            <Autocomplete
                                                id="vehicle"
                                                options={vehicleList}
                                                getOptionLabel={(option) => option.label}
                                                onChange={(e,value) => setData('vehicle_id', value.value)}
                                                renderInput={(params) => (
                                                    <TextField
                                                        {...params}
                                                        label="Veículo"
                                                        fullWidth
                                                        value={data.vehicle_id}
                                                        error={errors.vehicle_id}
                                                        helperText={errors.vehicle_id}
                                                    />
                                                )}
                                                sx={{ mb: 2 }}
                                            />
                                        </Grid>

                                        
                                        <Grid item xs={12} margin={'normal'}>
                                            <Autocomplete
                                                id="driver"
                                                options={driversList}
                                                getOptionLabel={(option) => option.label}
                                                onChange={(e,value) => setData('driver_id', value.value)}
                                                renderInput={(params) => (
                                                    <TextField
                                                        {...params}
                                                        label="Condutor"
                                                        fullWidth
                                                        value={data.driver_id}
                                                        error={errors.driver_id}
                                                        helperText={errors.driver_id}
                                                    />
                                                )}
                                                sx={{ mb: 2 }}
                                            />
                                        </Grid>

                                        
                                        <Grid item xs={12} margin={'normal'}>
                                            <Autocomplete
                                                id="techician"
                                                options={techniciansList}
                                                getOptionLabel={(option) => option.label}
                                                onChange={(e,value) => setData('technician_id', value.value)}
                                                renderInput={(params) => (
                                                    <TextField
                                                        {...params}
                                                        label="Técnico"
                                                        fullWidth
                                                        value={data.technician_id}
                                                        error={errors.technician_id}
                                                        helperText={errors.technician_id}
                                                    />
                                                )}
                                                sx={{ mb: 2 }}
                                            />
                                        </Grid>

                                    
                                        <Grid item xs={12}>
                                            <Button type="submit" variant="outlined" color="primary" disabled={processing}>
                                                Submeter
                                            </Button>
                                        </Grid>
                                
                                    </form> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}