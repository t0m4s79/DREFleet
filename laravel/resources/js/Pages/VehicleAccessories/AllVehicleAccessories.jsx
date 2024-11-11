import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import ErrorIcon from '@mui/icons-material/Error';
import { useEffect, useState } from 'react';
import { isBefore, parse } from 'date-fns';
import { DataGrid } from '@mui/x-data-grid';

const isExpired = (date) => {
    const parsedDate = typeof date.value === 'string' 
        ? parse(date.value, 'dd-MM-yyyy', new Date()) 
        : date.value;
    const now = new Date();

    if(parsedDate != null){
        if (isBefore(parsedDate, now)){
            return (
                <div style={{ color: 'red' }}>
                    <ErrorIcon style={{ marginRight: '4px', color: 'red', fontWeight: 'bolder' }} />
                    {date.formattedValue}
                </div>
            );
        } else return date.formattedValue
    } else {
        return null
    }
}

export default function AllVehicleAccessories( {auth, vehicleAccessories, flash}) {

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

    const vehicleAccessoryInfo = vehicleAccessories.map((vehicleAccessory) => {
        return {
            id: vehicleAccessory.id,
            name: vehicleAccessory.name,
            condition: vehicleAccessory.condition,
            expiration_date: vehicleAccessory.expiration_date,
            //vehicle_id: vehicleAccessory.vehicle_id,
            vehicle_id: vehicleAccessory.vehicle,
            created_at: vehicleAccessory.created_at,
            updated_at: vehicleAccessory.updated_at,
        }
    })

    const VehicleAccessoryColumnLabels = {
        id: 'ID',
        name: 'Nome',
        condition: 'Condição',
        expiration_date: 'Data de Validade',
        vehicle_id: 'Id do Veículo',
    };

    const vehicleAccColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'name',
            headerName: 'Nome',
            flex: 1,
        },
        {
            field: 'condition',
            headerName: 'Condição',
            flex: 1,
        },
        {
            field: 'expiration_date',
            headerName: 'Data de Validade',
            type: 'date',
            flex: 1,
            //maxWidth: 140,
            valueGetter: (params) => {
                console.log(params)
                if(params != '-') {
                    const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                    return parsedDate
                } else return null
            },
            renderCell: (params) => (isExpired(params)),
        },
        {
            field: 'vehicle_id',
            headerName: 'Veículo',
            flex: 1,
            disableColumnMenu: true,
            sortable: false,
            maxWidth: 100,
            renderCell: (params) => (
                <Link href={route('vehicles.showEdit', params.value.id)}>
                    <Button >{params.value.license_plate}</Button>
                </Link>
            )
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
            //maxWidth: 180,
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
            //maxWidth: 200,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
    ]

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Acessórios de Veículos</h2>}
        >

            <Head title="Acessórios" />

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('vehicleAccessories.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Acessório
                        </a>
                    </Button>

                    {/* <Table
                        data={vehicleAccessoryInfo}
                        columnsLabel={VehicleAccessoryColumnLabels}
                        editAction="vehicleAccessories.showEdit"
                        deleteAction="vehicleAccessories.delete"
                        dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                    /> */}

                    <DataGrid 
                        rows={vehicleAccessoryInfo}
                        columns={vehicleAccColumns}
                        density='compact'
                        getRowClassName={(params) => {
                            const expirationDate = parse(params.row.expiration_date, 'dd-MM-yyyy', new Date());
                            return expirationDate < new Date() ? 'expired-row' : '';
                        }}
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
