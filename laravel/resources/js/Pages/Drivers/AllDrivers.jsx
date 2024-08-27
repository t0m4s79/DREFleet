import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function AllDrivers( {auth, drivers, flash} ) {
    
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

    //Deconstruct data to send to table component
    let driverInfo = drivers.map((driver) => (
        {id: driver.user_id, name: driver.name, email: driver.email, phone: driver.phone, heavy_license: driver.heavy_license , status: driver.status }
    ))

    const driverColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        heavy_license: 'Carta de pesados',
        status: 'Estado'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutores</h2>}
        >

            <Head title="Condutores" />
        

            <div className='m-2 p-6'>

                <Button href={route('drivers.create')}>
                    <AddIcon />
                    <a  className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        Novo Condutor
                    </a>
                </Button>

                <Table data={driverInfo} columnsLabel={driverColumnLabels} editAction="drivers.showEdit" deleteAction="drivers.delete" dataId="user_id"/>

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