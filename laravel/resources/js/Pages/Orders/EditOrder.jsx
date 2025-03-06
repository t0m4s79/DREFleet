import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Autocomplete, Snackbar, Alert, Box } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import WaypointManager from './Partials/WaypointManager';
import { OrderContext, OrderProvider } from './OrderContext';
import axios from 'axios';
import { ErrorOutline, WarningAmber } from '@mui/icons-material';
import AccessibleIcon from '@mui/icons-material/Accessible';
import DriveEtaIcon from '@mui/icons-material/DriveEta';
import DirectionsBusIcon from '@mui/icons-material/DirectionsBus';
import LocalShippingIcon from '@mui/icons-material/LocalShipping';
import { parseVehicleExpirations } from '@/utils/Orders/vehicle';
import { parseDriverExpirations } from '@/utils/Orders/driver';

export default function EditOrder({ auth, order, drivers, vehicles, vehicleDocuments, vehicleAccessories, technicians, managers, kids, otherPlaces, orderRoutes, flash }) {
    return (
        <OrderProvider>
            <InnerEditOrder
                order={order}
                auth={auth}
                drivers={drivers}
                vehicles={vehicles}
                vehicleDocuments={vehicleDocuments}
                vehicleAccessories={vehicleAccessories}
                technicians={technicians}
                kids={kids}
                otherPlaces={otherPlaces}
                orderRoutes={orderRoutes}
                flash={flash}
            />
        </OrderProvider>
    );
}

function InnerEditOrder({ auth, order, drivers, vehicles, vehicleDocuments, vehicleAccessories, technicians, kids, otherPlaces, orderRoutes, flash }) {
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
    const [selectedRouteType, setSelectedRouteType] = useState('');
    const [selectedRouteID, setSelectedRouteID] = useState('');
    const [isPlacesModified, setIsPlacesModified] = useState(true); // TODO: create method to check if places were changed
    const [preferredDrivers, setPreferredDrivers] = useState([]);
    const [preferredTechnicians, setPreferredTechnicians] = useState([]);
    const [isEditMode, setisEditMode] = useState(false)

    const [openSnackbar, setOpenSnackbar] = useState(false);                // defines if snackbar shows or not
    const [snackbarMessage, setSnackbarMessage] = useState('');             // defines the message to be shown in the snackbar
    const [snackbarSeverity, setSnackbarSeverity] = useState('success');    // 'success' or 'error'

    useEffect(() => {
        if (flash.message || flash.error) {                                 // if there is a flash message/error
            setSnackbarMessage(flash.message || flash.error);               // set the message
            setSnackbarSeverity(flash.error ? 'error' : 'success');         // defines background color of snackbar
            setOpenSnackbar(true);                                          // show snackbar
        }
    }, [flash]);

    const orderStops = order.order_stops.map((stop) => {

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
                observations: order.observations ?? '',
            });

            //console.log('Form state initialized:', data);
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
        ...preferredDrivers.map((driver) => {
            const { expired, expiring } = parseDriverExpirations(driver);

            return {
                group: 'Condutores Habituais',
                value: driver.user_id,
                label: `#${driver.user_id} - ${driver.name}`,
                heavy_license: driver.heavy_license,
                heavy_license_type: driver.heavy_license_type,
                expiredCount: expired,
                expiringCount: expiring,
                tcc: driver.tcc
            };
        }),
        ...drivers.map((driver) => {
            const { expired, expiring } = parseDriverExpirations(driver);

            return {
                group: 'Todos os Condutores',
                value: driver.user_id,
                label: `#${driver.user_id} - ${driver.name}`,
                heavy_license: driver.heavy_license,
                heavy_license_type: driver.heavy_license_type,
                expiredCount: expired,
                expiringCount: expiring,
                tcc: driver.tcc
            };
        }),
    ];

    const vehicleList = vehicles.map((vehicle) => {
        const documents = vehicleDocuments.filter((document) => document.vehicle.id === vehicle.id);
        const accessories = vehicleAccessories.filter((accessory) => accessory.vehicle.id === vehicle.id);

        const { expired, expiring } = parseVehicleExpirations(documents, accessories);

        const wheelchair = vehicle.wheelchair_adapted === 1 && vehicle.wheelchair_certified === 1;

        return {
            value: vehicle.id,
            label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`,
            heavy_vehicle: vehicle.heavy_vehicle,
            heavy_type: vehicle.heavy_type,
            expiredCount: expired,
            expiringCount: expiring,
            wheelchair: wheelchair
        };
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
        observations: order.observations || ''
    }

    const { data, setData, put, patch, errors, processing } = useForm({ ...initialData })

    const toggleEdit = () => {
        if (isEditMode) {
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    const handleRouteChange = (route) => {
        //console.log(route)
        setSelectedRouteID(route.id)
        setPreferredDrivers(route.drivers)
        setPreferredTechnicians(route.technicians)
        setData('order_route_id', route.id)
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

    const updateSummary = (summary) => {
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

            //console.log('Updated form data with places and trajectory:', places, trajectory);
        }
    }, [places, trajectory]);


    const handleSubmit = async (e) => {
        e.preventDefault();

        // Debugging: Ensure data is ready before submitting
        //console.log('Form data on submit:', data);

        // Ensure the state is fully updated before submitting
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Submit the form
        put(route('orders.edit', order.id));
    };

    const handleApprove = (id) => {
        // manager_id is missing from the data
        // TODO: TO BE DISCUSSED WHERE THESE BUTTONS SHOULD APPEAR  
        try {
            patch(route('orders.approve', id))
        } catch (error) {
            console.error('Error approving order:', error)
        }
    }

    const handleUnapprove = (id) => {
        try {
            patch(route('orders.unapprove', id))
        } catch (error) {
            console.error('Error approving order:', error)
        }
    }

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
                                    {isEditMode === false ?
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


                                    {order.status === "Por aprovar" &&
                                        <>
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
                                        </>
                                    }
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
                                            options={['Transporte de Pessoal', 'Transporte de Mercadorias', 'Transporte de Crianças', 'Outros']}
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
                                                selectedDriver.heavy_license == 0 &&
                                                option.heavy_vehicle == 1
                                            );
                                        }}
                                        value={vehicleList.find(vehicle => vehicle.value === data.vehicle_id) || null}
                                        onChange={handleVehicleChange}
                                        renderOption={(props, option) => {
                                            return (
                                                <li key={option.value} {...props} style={{ display: "flex", alignItems: "center", width: "100%" }}>
                                                    <span>{option.label}</span>
                                                    <Box sx={{ display: "flex" }} className="ml-2">
                                                        {option.wheelchair && (
                                                            <AccessibleIcon />
                                                        )}
                                                        {option.heavy_vehicle === 1 ? (
                                                            option.heavy_type === "Mercadorias" ? (
                                                                <LocalShippingIcon />
                                                            ) : (
                                                                <DirectionsBusIcon />
                                                            )
                                                        ) : (
                                                            <DriveEtaIcon />
                                                        )}
                                                        {option.expiredCount > 0 && (
                                                            <ErrorOutline sx={{ color: "red" }} />
                                                        )}
                                                        {option.expiringCount > 0 && (
                                                            <WarningAmber sx={{ color: "orange" }} />
                                                        )}
                                                    </Box>
                                                </li>
                                            );
                                        }}
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
                                        renderOption={(props, option) => {

                                            return (
                                                <li key={option.value} {...props} style={{ display: "flex", alignItems: "center", width: "100%" }}>
                                                    <span>{option.label}</span>
                                                    <Box sx={{ display: "flex" }} className="ml-2">
                                                        {option.heavy_license === 1 ? (
                                                            option.heavy_license_type === "Mercadorias" ? (
                                                                <LocalShippingIcon />
                                                            ) : (
                                                                <DirectionsBusIcon />
                                                            )
                                                        ) : (
                                                            <DriveEtaIcon />
                                                        )}
                                                        {option.expiredCount > 0 && (
                                                            <ErrorOutline sx={{ color: "red" }} />
                                                        )}
                                                        {option.expiringCount > 0 && (
                                                            <WarningAmber sx={{ color: "orange" }} />
                                                        )}
                                                        {option.tcc === 0 && (
                                                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginLeft: "4px" }}>
                                                                <circle cx="12" cy="12" r="10" stroke="red" strokeWidth="2" />
                                                                <line x1="4" y1="4" x2="20" y2="20" stroke="red" strokeWidth="2" />
                                                                <path d="M12 7C13.1 7 14 6.1 14 5C14 3.9 13.1 3 12 3C10.9 3 10 3.9 10 5C10 6.1 10.9 7 12 7Z" fill="black" />
                                                                <path d="M9 9H15C16.1 9 17 9.9 17 11V15H14V12H10V15H7V11C7 9.9 7.9 9 9 9Z" fill="black" />
                                                            </svg>
                                                        )}
                                                    </Box>
                                                </li>
                                            );
                                        }}
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

                                <Grid item xs={12}>
                                    <InputLabel htmlFor="observations" sx={{ mb: 1 }}>
                                        Observações
                                    </InputLabel>
                                    <TextField
                                        id="observations"
                                        name="observations"
                                        disabled={!isEditMode}
                                        multiline
                                        rows={4}
                                        fullWidth
                                        value={data.observations || ''}
                                        onChange={(e) => {
                                            const newValue = e.target.value;
                                            if (newValue.length <= 500) {
                                                setData('observations', e.target.value)
                                            }
                                        }}
                                        error={Boolean(errors.observations)}
                                        helperText={errors.observations}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                                <div style={{ textAlign: 'right', color: data.observations.length >= 500 ? 'red' : 'black' }}>
                                    {500 - data.observations.length} caracteres restantes
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <Snackbar
                open={openSnackbar}
                autoHideDuration={3000}
                onClose={() => setOpenSnackbar(false)}
                anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}
            >
                <Alert variant='filled' onClose={() => setOpenSnackbar(false)} severity={snackbarSeverity} sx={{ width: '100%' }}>
                    {snackbarMessage}
                </Alert>
            </Snackbar>
        </AuthenticatedLayout>
    );
}