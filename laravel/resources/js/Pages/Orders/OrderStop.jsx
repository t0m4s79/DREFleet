import { OrderProvider, OrderContext } from "./OrderContext";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { TextField, Button, Grid, InputLabel, Snackbar, Alert, Input } from '@mui/material';
import { useState, useEffect, useContext } from "react";
import WaypointManager from "./Partials/WaypointManager";
import 'leaflet/dist/leaflet.css';

export default function OrderStop({ auth, order, kids, otherPlaces, orderRoutes, onlyView, flash }) {
    return (
        <OrderProvider>
            <InnerOrderStop
                auth={auth}
                order={order}
                kids={kids}
                otherPlaces={otherPlaces}
                orderRoutes={orderRoutes}
                onlyView={onlyView}
                flash={flash}
            />
        </OrderProvider>
    );
}


function InnerOrderStop({ auth, order, kids, otherPlaces, orderRoutes, onlyView, flash }) {

    const { updateWaypoints, updatePlaces, } = useContext(OrderContext);

    const [selectedRouteID, setSelectedRouteID] = useState('');

    const [openSnackbar, setOpenSnackbar] = useState(false);                // defines if snackbar shows or not
    const [snackbarMessage, setSnackbarMessage] = useState('');             // defines the message to be shown in the snackbar
    const [snackbarSeverity, setSnackbarSeverity] = useState('success');    // 'success' or 'error'

    useEffect(() => {
        if (flash && (flash.message || flash.error)) {                                 // if there is a flash message/error
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

    useEffect(() => {
        if (orderStops.length > 0) {

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
                observations: order.observations ?? '',
                action: null
            });

        }
        setSelectedRouteID(order.order_route_id)
    }, []);


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
        observations: order.observations || '',
        action: null
    }

    const { data, setData, put } = useForm({ ...initialData })

    const updateSummary = (summary) => {
        setData({
            ...data,  // Spread the existing form data
            expected_time: Number(summary.totalTime),
            distance: Number(summary.totalDistance),
        });
    }

    const stopService = async () => {
        data.action = 'stop';
        put(route('orders.stopOrder', order.id));
    }

    const finishService = async () => {
        data.action = 'finish';
        put(route('orders.stopOrder', order.id));
    }

    //console.log(order)

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Parar Serviço</h2>}
        >

            <Head title="Iniciar Serviço" />
            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        {onlyView &&
                            <h1 className="flex justify-center items-center text-3xl p-3">Estado do Pedido: {order.status}</h1>
                        }
                        <div className='p-6'>
                            <form>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <Grid item xs={12}>
                                    <InputLabel>Tipo de Transporte</InputLabel>
                                    <Input
                                        fullWidth
                                        value={order.order_type}
                                        disabled
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
                                        disabled={true}
                                        startForm={true}
                                    />

                                </Grid>

                                <Grid container spacing={3}>
                                    <Grid item xs={6}>
                                        <InputLabel sx={{ mt: 2 }}>Data e Hora de Início</InputLabel>

                                        <TextField
                                            id='expected_begin_date'
                                            name='expected_begin_date'
                                            fullWidth
                                            value={order.expected_begin_date}
                                            disabled
                                            sx={{ mb: 2 }}
                                        />
                                    </Grid>

                                    <Grid item xs={6}>
                                        <InputLabel sx={{ mt: 2 }}>Data e Hora de Fim</InputLabel>
                                        <TextField
                                            id='expected_end_date'
                                            name='expected_end_date'
                                            fullWidth
                                            value={order.expected_end_date}
                                            disabled
                                            sx={{ mb: 2 }}
                                        />
                                    </Grid>
                                </Grid>


                                <Grid item xs={12}>
                                    <InputLabel sx={{ mt: 2 }}>Veículo</InputLabel>
                                    <Input
                                        id="vehicle"
                                        fullWidth
                                        value={`${order.vehicle.make} ${order.vehicle.model} - ${order.vehicle.license_plate}`}
                                        disabled
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>


                                <Grid item xs={12} margin={'normal'}>
                                    <InputLabel sx={{ mt: 2 }}>Condutor</InputLabel>
                                    <Input
                                        id="driver"
                                        fullWidth
                                        value={`${order.driver.name} - ${order.driver.phone}`}
                                        disabled
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>


                                <Grid item xs={12} margin={'normal'}>
                                    <InputLabel sx={{ mt: 2 }}>Técnico</InputLabel>
                                    <Input
                                        id="techician"
                                        fullWidth
                                        value={`${order.technician.name} - ${order.technician.phone}`}
                                        disabled
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
                                        disabled
                                        multiline
                                        rows={4}
                                        fullWidth
                                        value={order.observations}
                                        sx={{ mb: 2 }}
                                    />

                                </Grid>

                                {!onlyView &&
                                    <Grid display="flex" flexDirection="row" justifyContent="end" gap="10px">
                                        <Grid item xs={12} display="flex" justifyContent="flex-end" marginTop={2}>
                                            <Button
                                                variant="contained"
                                                color="primary"
                                                size="large"
                                                onClick={stopService}
                                            >
                                                Interromper Serviço
                                            </Button>
                                        </Grid>

                                        <Grid item xs={12} display="flex" justifyContent="flex-end" marginTop={2}>
                                            <Button
                                                variant="contained"
                                                color="primary"
                                                size="large"
                                                onClick={finishService}
                                            >
                                                Finalizar Serviço
                                            </Button>
                                        </Grid>
                                    </Grid>
                                }

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
    )
}