import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { parse } from 'date-fns';
import { DataGrid } from '@mui/x-data-grid';
import { WarningAmber } from '@mui/icons-material';
import CustomDataGrid from '@/Components/CustomDataGrid';

const isRequestExceptional = (n) => {
    if (n > 6) {
        return (
            <div style={{ color: '#E8B012' }}>
                <WarningAmber />
                {n}
            </div>
        )
    } else {
        return n
    }
}

export default function AllVehicleRefuelRequest({ auth, requests, flash }) {
    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success');

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    const dataRequests = requests.map((request) => {
        return {
            id: request.id,
            date: request.date,
            vehicle_license_plate: request.vehicle.license_plate,
            kilometrage: request.kilometrage,
            quantity: request.quantity,
            cost_per_unit: request.cost_per_unit,
            total_cost: request.total_cost,
            fuel_type: request.fuel_type,
            vehicle_id: request.vehicle_id,
            created_at: request.created_at,
            updated_at: request.updated_at,
        }
    });

    const requestsColumns = [
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
            minWidth: 120,
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
            hideable: false
        },
        {
            field: 'kilometrage',
            headerName: 'Kilometragem',
            flex: 1,
            minWidth: 100,
            disableColumnMenu: true,
        },
        {
            field: 'quantity',
            headerName: 'Quantidade depositada',
            flex: 1,
            minWidth: 100,
            disableColumnMenu: true,
        },
        {
            field: 'cost_per_unit',
            headerName: 'Custo por unidade',
            flex: 1,
            minWidth: 100,
            disableColumnMenu: true,
        },
        {
            field: 'total_cost',
            headerName: 'Custo total',
            flex: 1,
            minWidth: 100,
            disableColumnMenu: true,
        },
        {
            field: 'fuel_type',
            headerName: 'Tipo de combustível',
            flex: 1,
            minWidth: 100,
            disableColumnMenu: true,
            sortable: false,
        },
        {
            field: 'vehicle_license_plate',
            headerName: 'Veículo',
            maxWidth: 100,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('vehicles.showEdit', params.row.vehicle_id)}
                    className='text-blue-500'
                >
                    {params.row.vehicle_license_plate}
                </Link>
            )
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
            minWidth: 160,
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
            minWidth: 160,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registos de Abastecimento</h2>}
        >

            {<Head title='Registos de Abastecimento do Veículo' />}



            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div className='bg-sky-600'>
                        <Button href={route('vehicleRefuelRequests.showCreate')}>
                            <AddIcon className='text-white' />
                            <a className="font-medium text-white dark:text-white hover:underline">
                                Novo Registo de Abastecimento
                            </a>
                        </Button>
                    </div>

                    <CustomDataGrid
                        rows={dataRequests}
                        columns={requestsColumns}
                        editAction="vehicleRefuelRequests.showEdit"
                        deleteAction="vehicleRefuelRequests.delete"
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