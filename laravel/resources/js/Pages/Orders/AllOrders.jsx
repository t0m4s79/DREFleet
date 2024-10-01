import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';

export default function AllOrders({auth, orders}) {
    console.log('orders', orders);

    const OrderInfo = orders.map((order)=>{
        // const beginLat = order.begin_coordinates.coordinates[1]
        // const beginLng = order.begin_coordinates.coordinates[0]

        // const endLat = order.end_coordinates.coordinates[1]
        // const endLng = order.end_coordinates.coordinates[0]
        
        return {
            id: order.id,
            vehicle_id: order.vehicle_id,
            driver_id: order.driver_id,
            technician_id: order.technician_id,
            begin_date: order.begin_date,
            end_date: order.begin_date,
            begin_address: order.begin_address,
            //begin_coordinates: {lat: beginLat, lng: beginLng},            
            end_address: order.end_address,
            //end_coordinates: {lat: endLat, lng: endLng},
            trajectory: order.trajectory,
            approved_by: order.manager_id,
            approved_date: order.approved_date,
        }
    })

    const orderColumnLabels = {
        id: 'ID',
        vehicle_id: 'Veículo',
        driver_id: 'Condutor',
        technician_id: 'Técnico',
        begin_date: 'Data de início',
        end_date: 'Data de fim',
        begin_address: 'Local de início',
        //begin_coordinates: 'Coordenadas de início',            
        end_address: 'Local de fim',
        //end_coordinates: 'Coordenadas de fim',
        trajectory: 'Rota',
        approved_by: 'Approvado por',
        approved_date: 'Data de aprovação',
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>}
        >

            <Head title="Pedidos" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-lg sm:rounded-lg">

                    <Button href={route('orders.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Pedido
                        </a>
                    </Button>

                    <Table data={OrderInfo} columnsLabel={orderColumnLabels} editAction={'orders.edit'} deleteAction={'orders.delete'} dataId={'id'}/>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}