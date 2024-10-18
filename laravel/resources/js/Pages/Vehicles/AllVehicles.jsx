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

    const vehicleInfo = vehicles.map((vehicle) => {
        return {
            id: vehicle.id,
            make: vehicle.make,
            model: vehicle.model,
            license_plate: vehicle.license_plate,
            year: vehicle.year,
            heavy_vehicle: vehicle.heavy_vehicle ? 'Sim' : 'Não',
            heavy_type: vehicle.heavy_type,
            wheelchair_adapted: vehicle.wheelchair_adapted ? 'Sim' : 'Não' ,
            wheelchair_certified: vehicle.wheelchair_certified ? 'Sim' : 'Não',
            capacity: vehicle.capacity,
            fuel_consumption: vehicle.fuel_consumption,
            status: vehicle.status,
            current_month_fuel_requests: vehicle.current_month_fuel_requests,
            fuel_type: vehicle.fuel_type,
            current_kilometrage: vehicle.current_kilometrage,
            vehicle_kilometrage_reports: vehicle.id,
            vehicle_accesories_docs: vehicle.id,
        }
    })

    const VehicleColumnLabels = {
        id: 'ID',
        make: 'Marca',
        model: 'Modelo',
        license_plate: 'Matrícula',
        year: 'Ano',
        heavy_vehicle: 'Veículo Pesado',
        heavy_type: 'Tipo de Pesado',
        wheelchair_adapted: 'Adapto a Cadeiras de Rodas',
        wheelchair_certified: 'Certificado para Cadeira de Rodas',
        capacity: 'Capacidade',
        fuel_consumption: 'Consumo',
        status: 'Estado',
        current_month_fuel_requests: 'Pedidos Mensais de Reabastecimento',
        fuel_type: 'Tipo de Combustível',
        current_kilometrage: 'Kilometragem Atual',
        vehicle_kilometrage_reports: 'Relatórios de Kilometragem',
        vehicle_accesories_docs: 'Documentos e Acessórios',
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículos</h2>}
        >

            <Head title="Veículos" />

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('vehicles.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Veículo
                        </a>
                    </Button>

                    <Table
                        data={vehicleInfo}
                        columnsLabel={VehicleColumnLabels}
                        editAction="vehicles.showEdit"
                        deleteAction="vehicles.delete"
                        dataId="id" // Ensure the correct field is passed for DataGrid's `id`
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
    )
}
