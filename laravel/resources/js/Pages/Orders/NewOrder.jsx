import { Head, Link, useForm } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Box, Autocomplete } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useState } from 'react';
import ExperimentalMap from '@/Components/ExperimentalMap';

export default function NewOrder({auth, drivers, vehicles, technicians, managers, kids, places}) {

    console.log('drivers', drivers);
    // console.log(vehicles);
    console.log('technicians', technicians);
    // console.log(managers);

    const [waypoints, setWaypoints] = useState([]);
    //const [newWaypoint, setNewWaypoint] = useState({label: '', lat: '', lng: '' });

    // Deconstruct places to change label display
    const placesList = places.map((place) => ({
        value: place.id,
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
        begin_date: '',
        end_date: '',
        vehicle_id: '',
        driver_id: '',
        technician_id: '',
        trajectory: []
    })

    const handleAddressChange = (index, value) => {
        const updatedWaypoints = [...waypoints];
        if (value) {
            updatedWaypoints[index] = { address: value.label, lat: value.lat, lng: value.lng };
        } else {
            updatedWaypoints[index] = { address: '', lat: 0, lng: 0 };
        }
        setWaypoints(updatedWaypoints);
    };

    const addWaypoint = () => {
        setWaypoints([...waypoints, { address: '', lat: 0, lng: 0 }]);
    };

    const removeLastWaypoint = () => {
        if (waypoints.length > 1) {
            setWaypoints(waypoints.slice(0, -1));
        }
    };

    const updateTrajectory= () => {

    }

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('orders.create'));
    };

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
                                            <Grid item xs={12} md={6} style={{overflowY: 'scroll'}}>
                                                <InputLabel value={'Pontos de paragem'} />
                                                <Grid item xs={12}>
                                                    {waypoints.map((_, index) => (
                                                        <Autocomplete
                                                            key={index}
                                                            options={placesList}
                                                            getOptionLabel={(option) => option.label}
                                                            onChange={(event, value) => handleAddressChange(index, value)}
                                                            renderInput={(params) => (
                                                                <TextField
                                                                    {...params}
                                                                    fullWidth
                                                                    label={`Waypoint ${index + 1}`}
                                                                />
                                                            )}
                                                        />
                                                    ))}
                                                </Grid>
                                                <Button onClick={addWaypoint}>Add Waypoint</Button>
                                                <Button onClick={removeLastWaypoint} disabled={waypoints.length <= 1}>
                                                    Remove Last Waypoint
                                                </Button>
                                                <Button onClick={() => setWaypoints([])}>Clear Route</Button>
                                            </Grid>
                    

                                        <Grid item xs={12} md={6}>
                                            {/* Passing waypoints to the map */}
                                            <ExperimentalMap waypoints={waypoints} onTrajectoryChange={updateTrajectory}/>
                                        </Grid>
                                    </Grid>
                    
                                    <Grid container spacing={3}>
                                        <Grid item xs={6}>
                                            <InputLabel htmlFor="data-hora-inicio" value="Data e Hora de Início" />
                                            <TextField
                                                //label="Data e Hora de Início"
                                                id='data-hora-inicio'
                                                name='data-hora-inicio'
                                                type="datetime-local"
                                                fullWidth
                                                value={data.begin_date}
                                                onChange={(e) => setData('begin_date', e.target.value)}
                                                error={errors.begin_date ? true : false}
                                                helperText={errors.begin_date}
                                            />
                                        </Grid>

                                        <Grid item xs={6}>
                                            <InputLabel htmlFor="data-hora-fim" value="Data e Hora de Fim" />
                                            <TextField
                                                // label="Data e Hora de Fim"
                                                id='data-hora-fim'
                                                name='data-hora-fim'
                                                type="datetime-local"
                                                fullWidth
                                                value={data.end_date}
                                                onChange={(e) => setData('end_date', e.target.value)}
                                                error={errors.end_date ? true : false}
                                                helperText={errors.end_date}
                                            />
                                        </Grid>
                                    </Grid>
                                   
                                    <Grid item xs={12}>
                                        <Autocomplete
                                            id="vehicle"
                                            options={vehicleList}
                                            getOptionLabel={(option) => option.label}
                                            onChange={(e) => setData('vehicle_id', e.target.value)}
                                            renderInput={(params) => (
                                                <TextField
                                                    {...params}
                                                    label="Veículo"
                                                    fullWidth
                                                    value={data.vehicle_id}
                                                    error={errors.vehicle_id ? true : false}
                                                    helperText={errors.vehicle_id}
                                                />
                                            )}
                                        />
                                    </Grid>

                                    
                                    <Grid item xs={12}>
                                        <Autocomplete
                                            id="driver"
                                            options={driversList}
                                            getOptionLabel={(option) => option.label}
                                            onChange={(e) => setData('driver_id', e.target.value)}
                                            renderInput={(params) => (
                                                <TextField
                                                    {...params}
                                                    label="Condutor"
                                                    fullWidth
                                                    value={data.driver_id}
                                                    error={errors.driver_id ? true : false}
                                                    helperText={errors.driver_id}
                                                />
                                            )}
                                        />
                                    </Grid>

                                    
                                    <Grid item xs={12}>
                                        <Autocomplete
                                            id="techician"
                                            options={techniciansList}
                                            getOptionLabel={(option) => option.label}
                                            onChange={(e) => setData('technician_id', e.target.value)}
                                            renderInput={(params) => (
                                                <TextField
                                                    {...params}
                                                    label="Técnico"
                                                    fullWidth
                                                    value={data.technician_id}
                                                    error={errors.technician_id ? true : false}
                                                    helperText={errors.technician_id}
                                                />
                                            )}
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