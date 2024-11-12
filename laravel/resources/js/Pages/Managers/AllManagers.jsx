import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import { Button, Snackbar, Alert, Chip  } from '@mui/material';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';

function renderStatus(status) {
    const colors = {
        'Disponível': 'success',
        'Em Serviço': 'warning',
        'Indisponível': 'error',
        'Escondido': 'default',
    };
  
    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllManagers({ auth, managers, flash }) {

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
    
    
    console.log(managers)
    //Deconstruct data to send to table component
    const managerInfo = managers.map((manager) => {         
        return {
            id: manager.id,
            name: manager.name,
            email: manager.email,
            phone: manager.phone,
            status: manager.status,
            all_approved_orders: manager.id,
        }
    })

    const managerColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Número de Telefone',
        status: 'Estado',
        all_approved_orders: 'Pedidos Aprovados'
    };

    const managerColumns = [
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
        {
            field: 'all_approved_orders',
            headerName: 'Pedidos Aprovados',
            flex: 1,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('managers.approved', params.value)}
                >
                    <Button                
                        variant="outlined"
                        sx={{
                            maxHeight: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        Consultar
                    </Button>
                </Link>
            )
        }
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Gestores</h2>}
        >

            <Head title="Gestores" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('managers.showCreate')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Gestor
                        </a>
                    </Button>

                    <Table data={managerInfo} columnsLabel={managerColumnLabels} editAction="managers.showEdit" deleteAction="managers.delete" dataId="id"/>
                
                    <DataGrid 
                        rows={managerInfo}
                        columns={managerColumns}
                        density='compact'
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