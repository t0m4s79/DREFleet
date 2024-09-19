import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';

export default function AllOrders({auth, orders}) {
    console.log(orders);
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>}
        >

            <Head title="Pedidos" />
        
            <div className="m-auto py-12 w-4/5">
                <div className="overflow-hidden shadow-lg sm:rounded-lg">

                    <Button href={route('orders.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                            Novo Pedido
                        </a>
                    </Button>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}