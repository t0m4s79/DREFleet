import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Autocomplete } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import WaypointManager from './Partials/WaypointManager';
import { OrderContext, OrderProvider } from './OrderContext';
import axios from 'axios';

export default function EditOrder({auth, order, drivers, vehicles, technicians, managers, kids, otherPlaces, orderRoutes}) {
    return (
        <OrderProvider>
            <InnerEditOrder
                order={order}
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

function InnerEditOrder({auth, order, drivers, vehicles, technicians, kids, otherPlaces, orderRoutes}) {
    //console.log('editOrder', order);
    const { 
        waypoints,
        places,
        trajectory,
        updateWaypoints,
        updatePlaces,
        updateTrajectory,
    } = useContext(OrderContext);

    const [selectedTechnician, setSelectedTechnician] = useState(null)
    const [selectedDriver, setSelectedDriver] = useState(null);
    const [selectedVehicle, setSelectedVehicle] = useState(null);
    const [selectedRouteType, setSelectedRouteType]= useState('');
    const [selectedRouteID, setSelectedRouteID] =useState('');
    const [isPlacesModified, setIsPlacesModified] = useState(true); // TODO: create method to check if places were changed
    const [preferredDrivers, setPreferredDrivers] = useState([]);
    const [preferredTechnicians, setPreferredTechnicians] = useState([]);
    const [isEditMode, setisEditMode] = useState(false)

    const orderStops = order.order_stops.map((stop)=> {

        const kidId = stop.kids.length > 0 ? stop.kids[0].id : null;    //Assuming there is only 1 kid per stop

        return { 
            place_id: stop.place_id,
            kid_id: kidId,
            label: `#${stop.place.id} - ${stop.place.address}`,
            lat: stop.place.coordinates.coordinates[1],
            lng: stop.place.coordinates.coordinates[0],
            stop_number: stop.stop_number,
            distance: stop.distance_from_previous_stop || 0, // Keep existing metric data if available
            time: stop.time_from_previous_stop || 0,         // Keep existing metric data if available
        }
    })
    //console.log('orderStops', orderStops)
    
    useEffect(() => {
        if (orderStops.length > 0) {
            //console.log('Initializing order stops:', orderStops);
    
            // Batch context updates together
            updateWaypoints(orderStops);
            updatePlaces(orderStops);
    
            // Update the form state after the context has been updated
            setData({
                expected_begin_date: order.expected_begin_date,
                expected_end_date: order.expected_end_date,
                expected_time: order.expected_time,
                distance: order.distance,
                order_type: order.order_type,
                vehicle_id: order.vehicle_id,
                driver_id: order.driver_id,
                technician_id: order.technician_id,
                trajectory: order.trajectory,
                order_route_id: order.order_route_id,
                places: [],
                places_changed: isPlacesModified,
            });
    
            console.log('Form state initialized:', data);
        }
        setSelectedRouteID(order.order_route_id)
        setSelectedRouteType(order.order_type)
    }, []);    

    // Deconstruct places to change label display
    const otherPlacesList = otherPlaces.map((place) => ({
        id: place.id,
        label: `#${place.id} - ${place.address}`,
        lat: place.coordinates.coordinates[1],
        lng: place.coordinates.coordinates[0],
    }));

    const driversList = [
        ...preferredDrivers.map((driver) => ({
            group: 'Condutores Habituais',
            value: driver.user_id,
            label: `#${driver.user_id} - ${driver.name}`,
            heavy_license: driver.heavy_license,
        })),
            ...drivers.map((driver) => ({
            group: 'Todos os Condutores',
            value: driver.user_id,
            label: `#${driver.user_id} - ${driver.name}`,
            heavy_license: driver.heavy_license,
        })),
      ];

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`, heavy_vehicle: vehicle.heavy_vehicle}
    })

    const techniciansList = [
        ...preferredTechnicians.map((technician) => ({
            group: 'Técnicos Habituais',
            value: technician.id,
            label: `#${technician.id} - ${technician.name}`,
        })),
        ...technicians.map((technician) => ({
            group: 'Todos os Técnicos',
            value: technician.id,
            label: `#${technician.id} - ${technician.name}`,
        })),
    ];

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

    const initialData = {
        expected_begin_date: order.expected_begin_date,
        expected_end_date: order.expected_end_date,
        expected_time: order.expected_time,
        distance: order.distance,
        order_type: order.order_type,
        vehicle_id: order.vehicle_id,
        driver_id: order.driver_id,
        technician_id: order.technician_id,
        trajectory: order.trajectory,
        order_route_id: order.order_route_id,
        places: [],
        places_changed: isPlacesModified,
    }

    const { data, setData, put, patch, errors, processing} = useForm({...initialData})

    const toggleEdit = () => {
        if (isEditMode) {
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    const handleRouteChange =(route) => {
        //console.log(route)
        setSelectedRouteID(route.id)
        setPreferredDrivers(route.drivers)
        setPreferredTechnicians(route.technicians)
        setData('order_route_id',route.id)
    }

    const handleRouteType = (type) => {
        setSelectedRouteType(type)
        setData('order_type', type)
    }

    const handleTechnicianChange = (e, value) => {
        setSelectedTechnician(value); // Save selected driver
        setData('technician_id', value?.value || '');
    };

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
    
        // Submit the form
        put(route('orders.edit', order.id));
    };

    const handleApprove = (id) => {
        // manager_id is missing from the data
        // TODO: TO BE DISCUSSED WHERE THESE BUTTONS SHOULD APPEAR  
        try{
            patch(route('orders.approve', id))
        } catch (error){
            console.error('Error approving order:', error)
        }
    }

    const handleUnapprove = (id) => {
        try{
            patch(route('orders.unapprove', id))
        } catch (error){
            console.error('Error approving order:', error)
        }
    }
    
//console.log(data)
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedido #{order.id}</h2>}
        >

            <Head title="Editar Pedido" />

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <form onSubmit={handleSubmit}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                
                                <div>
                                    { isEditMode === false ? 
                                        (<div className='mb-4'>
                                            <Button
                                            variant="contained"
                                            color="primary"
                                            disabled={processing}
                                            onClick={toggleEdit}
                                        >
                                            Editar
                                            </Button>
                                        </div>) : 

                                    (<div className='mb-4 space-x-4'>
                                        <Button 
                                            variant="outlined"
                                            color="error"
                                            disabled={processing}
                                            onClick={toggleEdit}
                                        >
                                            Cancelar Edição
                                        </Button>
                                        <Button
                                            type="submit"
                                            variant="outlined"
                                            color="primary"
                                            disabled={processing}
                                        >
                                            Submeter
                                        </Button>
                                    </div>)}
                                
                                
                                    <Button
                                        color="success"
                                        onClick={() => handleApprove(order.id)}
                                    >
                                        Aprovar
                                    </Button>
                                    <Button
                                        color="error"
                                        onClick={() => handleUnapprove(order.id)}
                                    >
                                        Não Aprovar
                                    </Button>
                                </div>

                                <Grid container spacing={3}>
                                    <Grid item xs={12}>

                                        <InputLabel sx={{ mb: 2 }}>Rota</InputLabel>
                                        <Autocomplete
                                            options={orderRoutes}
                                            getOptionLabel={(option) => option.name}
                                            value={orderRoutes.find(route => route.id === data.order_route_id) || null}
                                            onChange={(event, route) => handleRouteChange(route)}
                                            renderInput={(params) => <TextField {...params} label="Rota" />}
                                            error={errors.order_route_id}
                                            helperText={errors.order_route_id}
                                            disabled={!isEditMode}
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
                                            disabled={!isEditMode}
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
                                            updateSummary={updateSummary}
                                            selectedRoute={orderRoutes.find(route => route.id === selectedRouteID)}
                                            disabled={!isEditMode}
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
                                            disabled={!isEditMode}
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
                                            disabled={!isEditMode}
                                            sx={{ mb: 2 }}
                                        />
                                    </Grid>
                                </Grid>
                            
                                <Grid item xs={12}>
                                    <Autocomplete
                                        id="vehicle"
                                        options={vehicleList}
                                        getOptionDisabled={(option) => {
                                            // Disable vehicles that require a heavy license if the selected driver does not have one
                                            return (
                                                selectedDriver &&
                                                selectedDriver.heavy_license==0 &&
                                                option.heavy_vehicle==1
                                            );
                                        }}
                                        value={vehicleList.find(vehicle => vehicle.value === data.vehicle_id) || null}
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
                                        disabled={!isEditMode}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                                
                                <Grid item xs={12} margin={'normal'}>
                                    <Autocomplete
                                        id="driver"
                                        options={driversList}
                                        groupBy={(option) => option.group}
                                        getOptionLabel={(option) => option.label}
                                            getOptionDisabled={(option) => {
                                                // Disable drivers who don't have a heavy license if the selected vehicle requires one
                                                return (
                                                    selectedVehicle &&
                                                    selectedVehicle.heavy_vehicle &&
                                                    !option.heavy_license
                                                );
                                            }}
                                        value={driversList.find(driver => driver.value === data.driver_id) || null}
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
                                        disabled={!isEditMode}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                                
                                <Grid item xs={12} margin={'normal'}>
                                    <Autocomplete
                                        id="techician"
                                        options={techniciansList}
                                        groupBy={(option) => option.group}
                                        getOptionLabel={(option) => option.label}
                                        value={techniciansList.find(technician => technician.value === data.technician_id) || null}
                                        onChange={handleTechnicianChange}
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
                                        disabled={!isEditMode}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}