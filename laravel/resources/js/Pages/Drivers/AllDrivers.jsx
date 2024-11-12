import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Chip, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import ErrorIcon from '@mui/icons-material/Error';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { isBefore, parse } from 'date-fns';


const isExpired = (date) => {
    console.log(date)
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

function renderStatus(status) {
    const colors = {
        'Disponível': 'success',
        'Em Serviço': 'warning',
        'Indisponível': 'error',
        'Escondido': 'default',
    };
  
    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllDrivers( {auth, drivers, flash} ) {
    console.log(drivers);
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

    //Deconstruct data to send to table component
    let driverInfo = drivers.map((driver) => (
        {
            id: driver.user_id, 
            name: driver.name, 
            email: driver.email, 
            phone: driver.phone, 
            heavy_license: driver.heavy_license, //? 'Sim' : 'Não', 
            license_number: driver.license_number, 
            heavy_license_type: driver.heavy_license_type, 
            license_expiration_date: driver.license_expiration_date,
            tcc: driver.tcc,
            tcc_expiration_date: driver.tcc_expiration_date, 
            status: driver.status 
        }
    ))

    const driverColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Número de Telefone',
        license_number: 'Nº da Carta de Condução',
        heavy_license: 'Carta de Pesados',
        heavy_license_type: 'Tipo de Carta de Pesados',
        license_expiration_date: 'Data de Validade da Carta',
        tcc: 'TCC',
        tcc_expiration_date: 'Data de Validade de TCC',
        status: 'Estado',
    };

    const driverColumns = [
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
            field: 'email',
            headerName: 'Email',
            flex: 1,
        },
        {
            field: 'phone',
            headerName: 'Número de Telefone',
            flex: 1,
        },
        {
            field: 'license_number',
            headerName: 'Nº da Carta de Condução',
            flex: 1,
        },
        {
            field: 'heavy_license',
            headerName: 'Carta de Pesados',
            flex: 1,
            type: 'boolean',
            maxWidth: 100,
        },
        {
            field: 'heavy_license_type',
            headerName: 'Tipo de Carta de Pesados',
            flex: 1,
            renderCell: (params)=> (
                params.value != '-'? <Chip label={params.value} variant="outlined" size="small"/> : '-'
            ) 
        },
        {
            field: 'license_expiration_date',
            headerName: 'Data de Validade da Carta',
            type: 'date',
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
            renderCell: (params) => (isExpired(params)),
        },
        {
            field: 'tcc',
            headerName: 'TCC',
            flex: 1,
            type: 'boolean',
            maxWidth: 80,
        },
        {
            field: 'tcc_expiration_date',
            headerName: 'Data de Validade da TCC',
            type: 'date',
            flex: 1,
            valueGetter: (params) => {
                if(params != '-') {
                    const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                    return parsedDate
                } else return null
            },
            renderCell: (params) => (isExpired(params)),
        },
        {
            field: 'status',
            headerName: 'Estado',
            flex: 1,
            renderCell: (params) => (renderStatus(params.value))
        },
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutores</h2>}
        >

            <Head title="Condutores" />
        

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('drivers.showCreate')}>
                        <AddIcon />
                        <a  className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Condutor
                        </a>
                    </Button>

                    <Table data={driverInfo} columnsLabel={driverColumnLabels} editAction="drivers.showEdit" deleteAction="drivers.delete" dataId="user_id"/>
                
                    <DataGrid 
                        rows={driverInfo}
                        columns={driverColumns}
                        density='compact'
                        getRowClassName={(params) => {
                            const licenseExpirationDate = parse(params.row.license_expiration_date, 'dd-MM-yyyy', new Date());
                            const tccExpirationDate = parse(params.row.tcc_expiration_date, 'dd-MM-yyyy', new Date())
                            // Check if either date is before the current date
                            const isLicenseExpired = isBefore(licenseExpirationDate, new Date());
                            const isTccExpired = isBefore(tccExpirationDate, new Date());

                            return isLicenseExpired || isTccExpired ? 'expired-row' : '';
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
    );
}