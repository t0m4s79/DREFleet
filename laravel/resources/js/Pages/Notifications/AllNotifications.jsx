import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';
import { useNotifications } from './NotificationContext';

export default function AllNotifications({auth, notifications, flash}) {
    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'
    const [localNotifications, setLocalNotifications] = useState(notifications);

    const { fetchAllNotifications, markNotificationAsRead } = useNotifications();


    // const markAsRead = async (notificationId) => {
    //     try {
    //         const response = await fetch(route('notifications.markAsRead', notificationId), {
    //             method: 'PATCH',
    //             headers: {
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    //                 'Content-Type': 'application/json',
    //             },
    //         });

    //         const result = await response.json();
    //         setSnackbarMessage(result.message);
    //         setSnackbarSeverity('success');

    //         // Here, you can filter out the marked notification if you want to update the UI immediately
    //     } catch (error) {
    //         setSnackbarMessage(error.message);
    //         setSnackbarSeverity('error');
    //         console.log(error)
    //     } finally {
    //         setOpenSnackbar(true);
    //     }
    // };

    useEffect(() => {
        // Update local state when notifications prop changes
        setLocalNotifications(notifications);

        // Show flash messages in Snackbar
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [notifications, flash]);

    const handleMarkAsRead = async (notificationId) => {
        try {
            const response = await markNotificationAsRead(notificationId);
            console.log(response)
            if (response.status === 200 && response.data?.status === 'success') {
                // Update the local notifications state
                const updatedNotifications = localNotifications.map((notification) => {
                    if (notification.id === notificationId) {
                        return { ...notification, is_read: true };
                    }
                    return notification;
                });
                setLocalNotifications(updatedNotifications);
    
                // Show success message in Snackbar
                setSnackbarMessage(response.data.message);
                setSnackbarSeverity('success');
                setOpenSnackbar(true);
            } else {
                // Handle unexpected error
                setSnackbarMessage(response.data.message || 'Ocorreu um erro desconhecido.');
                setSnackbarSeverity('error');
                setOpenSnackbar(true);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
    
            // Show error message in Snackbar
            setSnackbarMessage(error.response?.data?.message || 'Houve um erro ao marcar a notificação como lida.');
            setSnackbarSeverity('error');
            setOpenSnackbar(true);
        }
    };

    const handleSnackbarClose = () => {
        setOpenSnackbar(false);
        //window.location.reload(); // Reload the page after Snackbar closes
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Notificações</h2>}
        >

            <Head title="Notificações" />

            <div className="my-12 max-w-full overflow-x-auto mx-6 shadow-sm sm:rounded-lg">
                <div>
                    <table className="min-w-full divide-y divide-gray-200 overflow-y-scroll">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recebido</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo (id)</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {localNotifications.map(notification => (
                                <tr key={notification.id}>
                                    <td className="px-6 py-4 max-w-[15%] whitespace-normal text-sm text-gray-900">{notification.created_at}</td>
                                    <td className="px-6 py-4 max-w-[20%] whitespace-normal text-sm text-gray-900">{notification.title}</td>
                                    <td className="px-6 py-4 max-w-[35%] whitespace-normal text-sm text-gray-900">{notification.message}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{notification.type}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{notification.related_entity_id}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {!notification.is_read && ( // Show button only if notification is unread
                                            <button
                                                onClick={() => handleMarkAsRead(notification.id)}
                                                className="text-blue-600 hover:underline"
                                            >
                                                Marcar como lida
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Snackbar 
                open={openSnackbar} 
                autoHideDuration={3000}
                onClose={handleSnackbarClose}
                anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}
            >
                <Alert variant='filled' onClose={() => setOpenSnackbar(false)} severity={snackbarSeverity} sx={{ width: '100%' }}>
                    {snackbarMessage}
                </Alert>
            </Snackbar>

        </AuthenticatedLayout>
    );
}