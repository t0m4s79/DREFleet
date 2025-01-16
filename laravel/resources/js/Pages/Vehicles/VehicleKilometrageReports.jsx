import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { parse } from 'date-fns';
import CustomDataGrid from '@/Components/CustomDataGrid';

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
    const vehicleReports = vehicle.kilometrage_reports.map((report) => {
        return {
            id: report.id,
            date: report.date,
            begin_kilometrage: report.begin_kilometrage,
            end_kilometrage: report.end_kilometrage,
            vehicle_id: report.vehicle_id,
            driver_id: report.driver_id,
            created_at: report.created_at,
            updated_at: report.updated_at,
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

    const vehicleKilometrageColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'date',
            headerName: 'Data',
            type: 'date',
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
            hideable: false
        },
        {
            field: 'begin_kilometrage',
            headerName: 'Kilometragem Inicial',
            flex: 1,
        },
        {
            field: 'end_kilometrage',
            headerName: 'Kilometragem Final',
            flex: 1,
        },
        {
            field: 'vehicle_id',
            headerName: 'Veículo',
            flex: 1,
            disableColumnMenu: true,
            sortable: false,
            maxWidth: 100,
            renderCell: (params) => (
                <Link href={route('vehicles.showEdit', params.value)}>
                    <Button >{vehicle.license_plate}</Button>
                </Link>
            )
        },
        {
            field: 'driver_id',
            headerName: 'Condutor',
            flex: 1,
            renderCell: (params) => (
                <Link
                    href={route('drivers.showEdit', params.value)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxWidth: '30px',
                            maxHeight: '30px',
                            minWidth: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value}     
                    </Button>    
                </Link>
            ),
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
            maxWidth: 180,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
        {
            field: 'updated_at',
            headerName: 'Data da Última Atualização',
            type: 'dateTime',
            flex: 1,
            maxWidth: 200,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registos de Kilometragem do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Registo de Kilometragem do Veículo' />}

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

                            <CustomDataGrid
                                rows={vehicleReports}
                                columns={vehicleKilometrageColumns}
                                editAction="vehicleKilometrageReports.showEdit"
                                deleteAction="vehicleKilometrageReports.delete"
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