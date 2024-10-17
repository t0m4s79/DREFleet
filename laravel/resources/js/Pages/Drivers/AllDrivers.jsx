import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

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
            heavy_license: driver.heavy_license ? 'Sim' : 'Não', 
            license_number: driver.license_number, 
            heavy_license_type: driver.heavy_license_type, 
            license_expiration_date: driver.license_expiration_date, 
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
        status: 'Estado',
    };
    
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