import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function VehicleKilometrageReports( {auth, vehicle, flash} ) {

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
    const vehicleReports = vehicle.kilometrage_reports.map((reports) => {
        return {
            id: reports.id,
            date: reports.date,
            begin_kilometrage: reports.begin_kilometrage,
            end_kilometrage: reports.end_kilometrage,
            vehicle_id: reports.vehicle_id,
            driver_id: reports.driver_id,
            created_at: reports.created_at,
            updated_at: reports.updated_at,
        }
    });

    const vehicleReportsColumnLabels = {
        id: 'ID',
        date: 'Data',
        begin_kilometrage: 'Kilometragem Inicial',
        end_kilometrage: 'Kilometragem Final',
        vehicle_id: 'Veículo',
        driver_id: 'Condutor',
        created_at: 'Data de Criação',
        updated_at: 'Data da Última Atualização',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registo de Kilometragem do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Registo de Kilometragem do Veículo' />}

            {/*TODO: TABLES WITH DOCUMENTS AND ACCESSORIES */}
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                            <Button href={route('vehicleKilometrageReports.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Nova Entrada
                                </a>
                            </Button>

                            <Table
                                data={vehicleReports}
                                columnsLabel={vehicleReportsColumnLabels}
                                editAction="vehicleKilometrageReports.showEdit"
                                deleteAction="vehicleKilometrageReports.delete"
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