import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import ErrorIcon from '@mui/icons-material/Error';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { isBefore, parse } from 'date-fns';

const isExpired = (date) => {
    const parsedDate = typeof date.value === 'string' 
        ? parse(date.value, 'dd-MM-yyyy HH:mm', new Date()) 
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

const displayData = (data) => {
    const textArray = data.value //.length > 0 ? data.value.split('\n') : []
    return (
        <div>
            {textArray.map((elem, index) => (
                <div key={index}>
                    <span>{elem}</span>
                </div>
            ))}
        </div>
    );
}


export default function AllVehicleDocuments( {auth, vehicleDocuments, flash}) {
console.log(vehicleDocuments);
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

    const vehicleDocumentInfo = vehicleDocuments.map((vehicleDocument) => {
        // Parse the JSON data field
        const additionalData = vehicleDocument.data
            ? Object.entries(vehicleDocument.data).map(([key, value]) => `${key}: ${value}`)
            : [];
    
        return {
            id: vehicleDocument.id,
            name: vehicleDocument.name,
            issue_date: vehicleDocument.issue_date,
            expiration_date: vehicleDocument.expiration_date,
            expired: vehicleDocument.expired,
            vehicle_id: vehicleDocument.vehicle,
            additionalData: additionalData, // Store key-value pairs as array
            created_at: vehicleDocument.created_at,
            updated_at: vehicleDocument.updated_at,
        };
    });

    const VehicleDocumentColumnLabels = {
        id: 'ID',
        name: 'Nome',
        issue_date: 'Data de emissão',
        expiration_date: 'Data de validade',
        expired: 'Expirado',
        vehicle_id: 'Id do veículo',
        additionalData: 'Dados adicionais',
    };

    const vehicleDocsColumns = [
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
            field: 'issue_date',
            headerName: 'Data de Emissão',
            type: 'date',
            flex: 1,
            maxWidth: 140,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
        },
        {
            field: 'expiration_date',
            headerName: 'Data de Validade',
            type: 'date',
            flex: 1,
            maxWidth: 140,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
            renderCell: (params) => (isExpired(params)),
        },
        {
            field: 'expired',
            headerName: 'Expirado',
            flex: 1,
            maxWidth: 100,
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
            field: 'additionalData',
            headerName: 'Dados Adicionais',
            flex: 1,
            display: 'flex',
            renderCell: (params) => (displayData(params))
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Documentos de Veículos</h2>}
        >

            <Head title="Documentos" />

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('vehicleDocuments.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Documento
                        </a>
                    </Button>

                    {/* <Table
                        data={vehicleDocumentInfo}
                        columnsLabel={VehicleDocumentColumnLabels}
                        editAction="vehicleDocuments.showEdit"
                        deleteAction="vehicleDocuments.delete"
                        dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                    /> */}

                    <DataGrid 
                        rows={vehicleDocumentInfo}
                        columns={vehicleDocsColumns}
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
