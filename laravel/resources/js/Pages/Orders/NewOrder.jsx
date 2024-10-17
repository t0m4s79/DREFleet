import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Autocomplete } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useCallback, useContext, useEffect, useState } from 'react';
import WaypointManager from './Partials/WaypointManager';
import { OrderContext, OrderProvider } from './OrderContext';

export default function NewOrder({auth, drivers, vehicles, technicians, managers, kids, otherPlaces, orderRoutes}) {
    return (
        <OrderProvider>
            <InnerNewOrder
                auth={auth}
                drivers={drivers}
                vehicles={vehicles}
                technicians={technicians}
                kids={kids}
                otherPlaces={otherPlaces}
                orderRoutes={orderRoutes}
            />
        </OrderProvider>
    );
}

function InnerNewOrder({ auth, drivers, vehicles, technicians, kids, otherPlaces, orderRoutes }) {
    const { 
        waypoints,
        places,
        trajectory,
        updateWaypoints,
        updatePlaces,
        updateTrajectory,
    } = useContext(OrderContext);

    const [selectedDriver, setSelectedDriver] = useState(null);
    const [selectedVehicle, setSelectedVehicle] = useState(null);
    const [selectedRouteType, setSelectedRouteType]= useState('');
    const [selectedRouteID, setSelectedRouteID] =useState('');

    // Deconstruct places to change label display
    const otherPlacesList = otherPlaces.map((place) => ({
        id: place.id,
        label: `#${place.id} - ${place.address}`,
        lat: place.coordinates.coordinates[1],
        lng: place.coordinates.coordinates[0],
    }));

    const driversList = drivers.map((driver) => {
        return {value: driver.user_id, label: `#${driver.user_id} - ${driver.name}`, heavy_license: driver.heavy_license}
    })

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`, heavy_vehicle: vehicle.heavy_vehicle}
    })

    const techniciansList = technicians.map((technician) => {
        return {value: technician.id, label: `#${technician.id} - ${technician.name}`}
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const { data, setData, post, errors, processing} = useForm({
        expected_begin_date: '',
        expected_end_date: '',
        expected_time: '',
        distance: '',
        order_type: '',
        vehicle_id: '',
        driver_id: '',
        technician_id: '',
        trajectory: trajectory,
        order_route_id: '',
        places: places,         //waypoints
    })

    const handleRouteChange =(route) => {
        setSelectedRouteID(route)
        setData('order_route_id',route)
    }

    const handleRouteType = (type) => {
        setSelectedRouteType(type)
        setData('order_type', type)
    }

    const handleDriverChange = (e, value) => {
        setSelectedDriver(value); // Save selected driver
        setData('driver_id', value?.value || '');
    };

    const handleVehicleChange = (e, value) => {
        setSelectedVehicle(value); // Save selected vehicle
        setData('vehicle_id', value?.value || '');
    };

    const updateSummary = ( summary ) => {
        //console.log('summary',summary);
        setData({
            ...data,  // Spread the existing form data
            expected_time: Number(summary.totalTime),
            distance: Number(summary.totalDistance),
        });
    }
    useEffect(() => {
        if (places && trajectory) {
            setData(prevData => ({
                ...prevData,
                places: places,
                trajectory: JSON.stringify(trajectory),
            }));
    
            console.log('Updated form data with places and trajectory:', places, trajectory);
        }
    }, [places, trajectory]);
    

    const handleSubmit = async (e) => {
        e.preventDefault();
    
        // Debugging: Ensure data is ready before submitting
        console.log('Form data on submit:', data);
    
        // Ensure the state is fully updated before submitting
        await new Promise(resolve => setTimeout(resolve, 1000));       
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
                                    
                                        <form onSubmit={handleSubmit}>
                                            <input type="hidden" name="_token" value={csrfToken} />
                                            
                                            <Grid container spacing={3}>
                                                <Grid item xs={12}>

                                                    <InputLabel sx={{ mb: 2 }}>Rota</InputLabel>
                                                    <Autocomplete
                                                        options={orderRoutes}
                                                        getOptionLabel={(option) => option.name}
                                                        value={orderRoutes.find(route => route.id === data.order_route_id) || null}
                                                        onChange={(event, route) => handleRouteChange(route?.id)}
                                                        renderInput={(params) => <TextField {...params} label="Rota" />}
                                                        error={errors.order_route_id}
                                                        helperText={errors.order_route_id}
                                                        sx={{ mb: 2 }}
                                                    />

                                                    <InputLabel sx={{ mb: 2 }}>Tipo de Transporte</InputLabel>
                                                    <Autocomplete
                                                        options={['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']}
                                                        value={data.order_type} // Bind the selected string
                                                        onChange={(event, value) => handleRouteType(value)}
                                                        //onChange={(event, value) => setData('order_type', value || '')} // Set the selected value (string)
                                                        renderInput={(params) => <TextField {...params} label="Tipo de Transporte" />}
                                                        error={errors.order_type}
                                                        helperText={errors.order_type}
                                                        sx={{ mb: 2 }}
                                                    />
                                                </Grid>
                                                <Grid item xs={12}>
                                                    <WaypointManager 
                                                        kids={kids} 
                                                        otherPlacesList={otherPlaces.map(place => ({
                                                            place_id: place.id,
                                                            label: `#${place.id} - ${place.address}`,
                                                            lat: place.coordinates.coordinates[1],
                                                            lng: place.coordinates.coordinates[0],
                                                        }))} 
                                                        onUpdateWaypoints={updateWaypoints} 
                                                        waypointsList={waypoints}
                                                        updateTrajectory={updateTrajectory}
                                                        updateSummary={updateSummary} 
                                                        selectedRoute={orderRoutes.find(route => route.id === selectedRouteID)}
                                                    />
                                                </Grid>
                                            </Grid>
                        
                                            <Grid container spacing={3}>
                                                <Grid item xs={6}>
                                                    <InputLabel htmlFor="expected_begin_date" value="Data e Hora de Início" />
                                                    <TextField
                                                        //label="Data e Hora de Início"
                                                        id='expected_begin_date'
                                                        name='expected_begin_date'
                                                        type="datetime-local"
                                                        fullWidth
                                                        value={data.expected_begin_date}
                                                        onChange={(e) => setData('expected_begin_date', e.target.value)}
                                                        error={errors.expected_begin_date}
                                                        helperText={errors.expected_begin_date}
                                                        sx={{ mb: 2 }}
                                                    />
                                                </Grid>

                                                <Grid item xs={6}>
                                                    <InputLabel htmlFor="expected_end_date" value="Data e Hora de Fim" />
                                                    <TextField
                                                        // label="Data e Hora de Fim"
                                                        id='expected_end_date'
                                                        name='expected_end_date'
                                                        type="datetime-local"
                                                        fullWidth
                                                        value={data.expected_end_date}
                                                        onChange={(e) => setData('expected_end_date', e.target.value)}
                                                        error={errors.expected_end_date}
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
                                                    getOptionDisabled={(option) => {
                                                        // Disable vehicles that require a heavy license if the selected driver does not have one
                                                        return (
                                                            selectedDriver &&
                                                            selectedDriver.heavy_license==0 &&
                                                            option.heavy_vehicle==1
                                                        );
                                                    }}
                                                    onChange={handleVehicleChange}
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
                                                    getOptionDisabled={(option) => {
                                                        // Disable drivers who don't have a heavy license if the selected vehicle requires one
                                                        return (
                                                            selectedVehicle &&
                                                            selectedVehicle.heavy_vehicle &&
                                                            !option.heavy_license
                                                        );
                                                    }}
                                                    onChange={handleDriverChange}
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