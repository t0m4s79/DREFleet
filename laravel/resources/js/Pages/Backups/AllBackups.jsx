import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { Head, useForm } from '@inertiajs/react';
import React, { useEffect, useState } from 'react';
import CustomDataGrid from '@/Components/CustomDataGrid';

export default function AllBackups({ auth, backups, flash, permissions }) {
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

    const { post } = useForm();

    const handleBackup = () => {
        post(route('backups.create'));
    };

    const backupsColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 80
        },
        {
            field: 'filename',
            headerName: 'Nome do Backup',
            flex: 2
        },
        {
            field: 'created_at',
            headerName: 'Data',
            flex: 1
        },
        { 
            field: 'size', 
            headerName: 'Tamanho', 
            flex: 1 
        },
        {
            field: 'user',
            headerName: 'Criado Por',
            flex: 1
        },
        {
            field: 'url',
            headerName: 'Download',
            flex: 1,
            renderCell: (params) => {
                return (
                    <a href={params.value} className="text-blue-500 underline">
                        Baixar
                    </a>
                );
            }
        },
    ];

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Backups</h2>}>
            <Head title='Backups' />
            <div className='py-12 px-6'>
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <Button onClick={handleBackup} className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                        <AddIcon />
                        Novo Backup
                    </Button>

                    <div className="mt-4">
                        <CustomDataGrid
                            rows={backups}
                            columns={backupsColumns}
                            deleteAction="backups.delete"
                            permissions={permissions}
                        />
                    </div>
                </div>
            </div>

            <Snackbar open={openSnackbar} autoHideDuration={3000} onClose={() => setOpenSnackbar(false)} anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}>
                <Alert variant='filled' onClose={() => setOpenSnackbar(false)} severity={snackbarSeverity} sx={{ width: '100%' }}>
                    {snackbarMessage}
                </Alert>
            </Snackbar>
        </AuthenticatedLayout>
    );
}
