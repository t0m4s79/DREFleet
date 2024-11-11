import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
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
    const textArray = data.value.length>0 ? data.value.split('\n') : []
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

export default function VehicleAccessoriesAndDocuments( {auth, vehicle, flash} ) {
    console.log(vehicle)
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
    const vehicleDocs = vehicle.documents.map((doc) => {
        // Parse the JSON data field for each document
        const additionalData = doc.data
            ? Object.entries(doc.data).map(([key, value]) => `${key}: ${value}`).join("\n")
            : [];
    
        return {
            id: doc.id,
            name: doc.name,
            issue_date: doc.issue_date,
            expiration_date: doc.expiration_date,
            expired: doc.expired,
            additionalData: additionalData,
            created_at: doc.created_at,
            updated_at: doc.updated_at,
        };
    });

    const vehicleDocsColumnLabels = {
        id: 'ID',
        name: 'Nome',
        issue_date: 'Data de Emissão',
        expiration_date: 'Data de Validade',
        expired: 'Expirado',
        additionalData: 'Dados adicionais',
        created_at: 'Data de Criação',
        updated_at: 'Data da Última Atualização',
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
    
    const vehicleAccessories = vehicle.accessories.map((acc) => {
        return {
            id: acc.id,
            name: acc.name,
            condition: acc.condition,
            expiration_date: acc.expiration_date,
            created_at: acc.created_at,
            updated_at: acc.updated_at,
        }
    });

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

    const vehicleAccColumnLabels = {
        id: 'ID',
        name: 'Nome',
        condition: 'Condição',
        expiration_date: 'Data de Validade',
        created_at: 'Data de Criação',
        updated_at: 'Data da Última Atualização',

    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Documentos e Accessórios do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Documentos e Acessórios de Veículo' />}

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                            <Button href={route('vehicleDocuments.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Documento
                                </a>
                            </Button>

                            <Table
                                data={vehicleDocs}
                                columnsLabel={vehicleDocsColumnLabels}
                                getRowHeight={() => 'auto'}
                                editAction="vehicleDocuments.showEdit"
                                deleteAction="vehicleDocuments.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />

                            <DataGrid
                                columns={vehicleDocsColumns}
                                rows={vehicleDocs}
                                disableRowSelectionOnClick
                                hideFooterSelectedRowCount
                                //localeText={ptPT.components.MuiDataGrid.defaultProps.localeText}
                                getRowClassName={(params) => {
                                    const expirationDate = parse(params.row.expiration_date, 'dd-MM-yyyy', new Date());
                                    return expirationDate < new Date() ? 'expired-row' : '';
                                }}
                            />
                        </div>
                    </div>

                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <Button href={route('vehicleAccessories.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Acessório
                                </a>
                            </Button>

                            <Table
                                data={vehicleAccessories}
                                columnsLabel={vehicleAccColumnLabels}
                                editAction="vehicleAccessories.showEdit"
                                deleteAction="vehicleAccessories.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />

                            <DataGrid
                                columns={vehicleAccColumns}
                                rows={vehicleAccessories}
                                disableRowSelectionOnClick
                                hideFooterSelectedRowCount
                                //localeText={ptPT.components.MuiDataGrid.defaultProps.localeText}
                                getRowClassName={(params) => {
                                    const expirationDate = parse(params.row.expiration_date, 'dd-MM-yyyy', new Date());
                                    return expirationDate < new Date() ? 'expired-row' : '';
                                }}
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