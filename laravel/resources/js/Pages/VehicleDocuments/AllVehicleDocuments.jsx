import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

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
            expired: vehicleDocument.expired ? 'Sim' : 'Não', // Handle boolean for display
            vehicle_id: vehicleDocument.vehicle_id,
            data: additionalData, // Store key-value pairs as array
        };
    });

    const VehicleDocumentColumnLabels = {
        id: 'ID',
        name: 'Nome',
        issue_date: 'Data de emissão',
        expiration_date: 'Data de validade',
        expired: 'Expirado',
        vehicle_id: 'Id do veículo',
        data: 'Dados adicionais',
    };

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

                    <Table
                        data={vehicleDocumentInfo}
                        columnsLabel={VehicleDocumentColumnLabels}
                        editAction="vehicleDocuments.showEdit"
                        deleteAction="vehicleDocuments.delete"
                        dataId="id" // Ensure the correct field is passed for DataGrid's `id`
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
