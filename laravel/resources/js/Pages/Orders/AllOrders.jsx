import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import { useEffect, useState } from 'react';
import CustomDataGrid from '@/Components/CustomDataGrid';
import { parseOrderColumns } from '@/utils/Orders/columns';


export default function AllOrders({ auth, orders, flash }) {

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

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        // Pad the numbers with leading zeros if needed
        return `${String(hours).padStart(2, '0')}h${String(minutes).padStart(2, '0')}m`;
    };

    const OrderInfo = orders.map((order) => {
        return {
            id: order.id,
            expected_begin_date: order.expected_begin_date,
            expected_end_date: order.expected_end_date,
            vehicle_id: order.vehicle.id,
            vehicle_license_plate: order.vehicle.license_plate,
            driver_id: order.driver.user_id,
            driver_name: order.driver.name,
            technician_id: order.technician.id,
            technician_name: order.technician.name,
            route: order.order_route_id,
            order_type: order.order_type,
            observations: order.observations,
            stops: order.order_stops.length,
            trajectory: order.trajectory,
            expected_time: formatTime(order.expected_time), // Convert expected time to hh:mm
            distance: parseFloat(order.distance),
            occurrences: order.occurrences,
            occurrences_count: order.occurrences.length, // Number of occurrences
            approved_date: order.approved_date,
            approved_by: order.manager_id,
            status: order.status,
            created_at: order.created_at,
            updated_at: order.updated_at,
        };
    });

    const orderColumns = parseOrderColumns(auth.user);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>}
        >

            <Head title="Pedidos" />

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    {(auth.user.user_type === "Administrador" || auth.user.user_type === "Gestor") &&
                        <div className='bg-sky-600'>
                            <Button href={route('orders.showCreate')}>
                                <AddIcon className='text-white'/>
                                <a className="font-medium text-white dark:text-white hover:underline">
                                    Novo Pedido
                                </a>
                            </Button>
                        </div>
                    }

                    <CustomDataGrid
                        rows={OrderInfo}
                        columns={orderColumns}
                        editAction={'orders.edit'}
                        deleteAction={'orders.delete'}
                        duplicateAction={'orders.duplicate'}
                        user={auth.user}
                    />
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