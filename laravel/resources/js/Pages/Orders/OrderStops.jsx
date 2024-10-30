import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';

{/**TODO: KID SHOWING IN STOP IF ASSOCIATED WITH IT */}
export default function OrderOccurences({auth, order}) {

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        // Pad the numbers with leading zeros if needed
        return `${String(hours).padStart(2, '0')}h${String(minutes).padStart(2, '0')}m`;
    };

    const orderStopsInfo = order.order_stops.map((stop) => {
        return {
            id: stop.id,
            arrival_date: stop.actual_arrival_date,
            expected_date: stop.expected_arrival_date,
            stop_number: stop.stop_number,
            time_previous_stop: formatTime(stop.time_from_previous_stop),
            distance_previous_stop: (stop.distance_from_previous_stop/ 1000).toFixed(2) + 'km',
            place_id: stop.place_id,
            /**falta kid */
        };
    });

    const orderStopsLabels = {
        id: 'ID',
        arrival_date: 'Data de chegada real',
        expected_date: 'Data de chegada prevista',
        stop_number: 'Número de paragem',
        time_previous_stop: 'Tempo esperado desde paragem anterior',
        distance_previous_stop: 'Distância desde paragem anterior',
        place_id: 'Morada',
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Paragens do Pedido #{order.id}</h2>}
        >

            <Head title="Paragens do Pedido" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Table data={orderStopsInfo} columnsLabel={orderStopsLabels} dataId={'id'}/>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}