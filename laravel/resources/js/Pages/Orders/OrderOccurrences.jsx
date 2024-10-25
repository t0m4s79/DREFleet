import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';

export default function OrderOccurences({auth, order, flash}) {
    console.log(order)
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

    const orderOccurrencesInfo = order.occurrences.map((occurrence) => {
        return {
            id: occurrence.id,
            date: order.expected_begin_date,
            order_id: occurrence.order_id,
            driver_id: order.driver.user_id,
            vehicle_id: order.vehicle.id,
            type: occurrence.type,
            description: 'Pop-up com descrição da ocorrência',
            created_at: occurrence.created_at,
            updated_at: occurrence.updated_at,
        };
    });

    const orderOccurrencesLabels = {
        id: 'ID',
        date: 'Data',
        order_id: 'Pedido',
        driver_id: 'Condutor',
        vehicle_id: 'Veículo',
        type: 'Tipo',
        description: 'Descrição',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização'
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Ocorrências do Pedido #{order.id}</h2>}
        >

            <Head title="Ocorrências do Pedido" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('orderOccurrences.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Occorrência
                        </a>
                    </Button>

                    <Table data={orderOccurrencesInfo} columnsLabel={orderOccurrencesLabels} editAction={'orderOccurrences.edit'} deleteAction={'orderOccurrences.delete'} dataId={'id'}/>
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