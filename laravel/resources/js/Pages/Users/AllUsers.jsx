import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import { Button, Snackbar, Alert, Chip } from '@mui/material';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import CustomDataGrid from '@/Components/CustomDataGrid';

function renderStatus(status) {
    const colors = {
        'Disponível': 'success',
        'Em Serviço': 'warning',
        'Indisponível': 'error',
        'Escondido': 'default',
    };
  
    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllDrivers( {auth, users, flash} ) {
    
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
    let userInfo = users.map((user) => (
        {id: user.id, name: user.name, email: user.email, phone: user.phone, user_type: user.user_type , status: user.status }
    ))

    const userColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Número de Telefone',
        user_type: 'Tipo de Utilizador',
        status: 'Estado'
    };

    const userColumns = [
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
            field: 'user_type',
            headerName: 'Tipo de utilizador',
            flex: 1,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Utilizadores</h2>}
        >

            <Head title="Utilizadores" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('users.showCreate')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Utilizador
                        </a>
                    </Button>

                    <Table data={userInfo} columnsLabel={userColumnLabels} editAction="users.showEdit" deleteAction="users.delete" dataId="id"/>
                
                    <CustomDataGrid
                        rows={userInfo}
                        columns={userColumns}
                        editAction="users.showEdit"
                        deleteAction="users.delete"
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