import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddIcon from '@mui/icons-material/Add';
import { Head } from '@inertiajs/react';
import { Button, Snackbar, Alert, Chip  } from '@mui/material';
import { useEffect, useState } from 'react';
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

export default function AllAdmins({ auth, admins, flash }) {

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
    
    const adminsInfo = admins.map((admins) => {   
        return {
            id: admins.id,
            name: admins.name,
            email: admins.email,
            phone: admins.phone,
            status: admins.status,
        }
    })

    const adminsColumns = [
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
            field: 'status',
            headerName: 'Estado',
            flex: 1,
            renderCell: (params) => (renderStatus(params.value))
        },
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Administradores</h2>}
        >

            <Head title="Técnicos" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('admins.showCreate')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Administrador
                        </a>
                    </Button>
                
                    <CustomDataGrid 
                        rows={adminsInfo}
                        columns={adminsColumns}
                        editAction="admins.showEdit" 
                        deleteAction="admins.delete"
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