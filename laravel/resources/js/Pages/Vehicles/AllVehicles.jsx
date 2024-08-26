import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function AllVehicles( {auth, vehicles, flash}) {

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

    console.log('vehicles', vehicles)
    let cols;

    if(vehicles.length > 0){
        cols = Object.keys(vehicles[0]);
    }

    const VehicleColumnLabels = {
        id: 'ID',
        make: 'Marca',
        model: 'Modelo',
        license_plate: 'Matricula',
        heavy_vehicle: 'Veiculo Pesado',
        wheelchair_adapted: 'Adapto a Cadeiras de Rodas',
        capacity: 'Capacidade',
        fuel_consumption: 'Consumo',
        status: 'Estado',
        current_month_fuel_requests: 'Pedidos de Reabastecimento (Este mes)'
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículos</h2>}
        >

            <Head title="Veículos" />

            <Button href={route('vehicles.create')}>
                <AddIcon />
                <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Novo Veículo
                </a>
            </Button>

            {vehicles && cols && <Table data={vehicles} columns={cols} columnsLabel={VehicleColumnLabels} editAction="vehicles.showEdit" deleteAction="vehicles.delete"/>}

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
