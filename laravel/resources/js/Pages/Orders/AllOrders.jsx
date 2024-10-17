import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';

export default function AllOrders({auth, orders, flash}) {

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

    console.log(orders);
    const OrderInfo = orders.map((order)=>{
        
        return {
            id: order.id,
            expected_begin_date: order.expected_begin_date,
            expected_end_date: order.expected_end_date,
            vehicle_id: order.vehicle_id,
            driver_id: order.driver_id,
            technician_id: order.technician_id,
            route: order.order_route_id,
            order_type: order.order_type,
            number_of_stops: order.order_stops.length,
            trajectory: order.trajectory,
            expected_time: formatTime(order.expected_time), // Convert expected time to hh:mm:ss
            distance: (order.distance / 1000).toFixed(2) + 'km',
            approved_date: order.approved_date,
            approved_by: order.manager_id,
            status: order.status,
            created_at: order.created_at,
            updated_at: order.updated_at,
        }
    })

    const orderColumnLabels = {
        id: 'ID',
        expected_begin_date: 'Data de início',
        expected_end_date: 'Data de fim',
        vehicle_id: 'Veículo',
        driver_id: 'Condutor',
        technician_id: 'Técnico',
        route: 'Rota',
        order_type: 'Tipo',
        number_of_stops: 'Número de Paragens',
        trajectory: 'Trajeto',
        expected_time: 'Tempo de Viagem Esperado',
        distance: 'Distância',
        approved_date: 'Data de aprovação',
        approved_by: 'Aprovado por',
        status: 'Estado',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização',
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>}
        >

            <Head title="Pedidos" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('orders.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Pedido
                        </a>
                    </Button>

                    <Table data={OrderInfo} columnsLabel={orderColumnLabels} editAction={'orders.edit'} deleteAction={'orders.delete'} dataId={'id'}/>
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