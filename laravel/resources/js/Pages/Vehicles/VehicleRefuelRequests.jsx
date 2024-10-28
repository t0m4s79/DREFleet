import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function VehicleKilometrageReports( {auth, vehicle, flash} ) {
    console.log(vehicle);
    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    //Deconstruct props to send to Table
    const vehicleRequests = vehicle.refuel_requests.map((request) => {
        return {
            id: request.id,
            date: request.date,
            kilometrage: request.kilometrage,
            quantity: request.quantity,
            cost_per_unit: request.cost_per_unit,
            total_cost: request.total_cost,
            fuel_type: request.fuel_type,
            monthly_request_number: request.monthly_request_number,
            request_type: request.request_type,
            vehicle_id: request.vehicle_id,
            created_at: request.created_at,
            updated_at: request.updated_at,
        }
    });

    const vehicleRequestsColumnLabels = {
        id: 'ID',
        date: 'Data',
        kilometrage: 'Kilometragem',
        quantity: 'Quantidade depositada',
        cost_per_unit: 'Custo por unidade',
        total_cost: 'Custo total',
        fuel_type: 'Tipo de combustível',
        monthly_request_number: 'Pedido mensal número',
        request_type: 'Tipo de pedido',
        vehicle_id: 'Veículo',
        created_at: 'Data de Criação',
        updated_at: 'Data da Última Atualização',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos de Reabastecimento do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Pedidos de Reabastecimento do Veículo' />}

            {/*TODO: TABLES WITH DOCUMENTS AND ACCESSORIES */}
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                            <Button href={route('vehicleRefuelRequests.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Pedido de Reabastecimento
                                </a>
                            </Button>

                            <Table
                                data={vehicleRequests}
                                columnsLabel={vehicleRequestsColumnLabels}
                                editAction="vehicleRefuelRequests.showEdit"
                                deleteAction="vehicleRefuelRequests.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />
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