import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';

export default function AllNotifications({auth, notifications, flash}) {

    console.log(notifications);

    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Notificações</h2>}
        >

            <Head title="Notificações" />

            <div className="mt-12 max-w-full overflow-x-auto mx-6 shadow-sm sm:rounded-lg">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recebido</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo (id)</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {notifications.map(notification => (
                            <tr key={notification.id}>
                                <td className="px-6 py-4 max-w-[15%] whitespace-normal text-sm text-gray-900">{notification.created_at}</td>
                                <td className="px-6 py-4 max-w-[20%] whitespace-normal text-sm text-gray-900">{notification.title}</td>
                                <td className="px-6 py-4 max-w-[35%] whitespace-normal text-sm text-gray-900">{notification.message}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{notification.type}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{notification.related_entity_id}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
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