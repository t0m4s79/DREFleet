import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { parse } from 'date-fns';
import CustomDataGrid from '@/Components/CustomDataGrid';

export default function OrderOccurences({auth, order}) {

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        // Pad the numbers with leading zeros if needed
        return `${String(hours).padStart(2, '0')}h${String(minutes).padStart(2, '0')}m`;
    };

    const orderStopsInfo = order.order_stops.map((stop) => {
        console.log(stop)
        const kid = stop.kids.length > 0 ? stop.kids[0] : null;    //Assuming there is only 1 kid per stop

        return {
            id: stop.id,
            arrival_date: stop.actual_arrival_date,
            expected_date: stop.expected_arrival_date,
            stop_number: stop.stop_number,
            time_previous_stop: formatTime(stop.time_from_previous_stop),
            distance_previous_stop: (stop.distance_from_previous_stop/ 1000).toFixed(2) + 'km',
            place_id: stop.place_id,
            kid_id: kid,
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

    const orderStopsColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'arrival_date',
            headerName: 'Data de chegada real',
            type: 'dateTime',
            flex: 1,
            valueGetter: (params) => {
                if(params){
                    const parsedDate = parse(params, 'dd-MM-yyyy HH:mm', new Date());
                    return parsedDate
                } else return null
            },
            
        },
        {
            field: 'expected_date',
            headerName: 'Data de chegada prevista',
            type: 'dateTime',
            flex: 1,
            valueGetter: (params) => {
                if(params){
                    const parsedDate = parse(params, 'dd-MM-yyyy HH:mm', new Date());
                    return parsedDate
                } else return null
            },
        },
        {
            field: 'stop_number',
            headerName: 'Número de paragem',
            flex: 1,
            maxWidth: 150,
        },
        {
            field: 'time_previous_stop',
            headerName: 'Tempo estimado desde paragem anterior',
            flex: 1,
        },
        {
            field: 'distance_previous_stop',
            headerName: 'Distância desde paragem anterior',
            flex: 1,
        },
        {
            field: 'place_id',
            headerName: 'Morada',
            flex: 1,
            maxWidth: 150,
            renderCell: (params) => (
                <Link
                    key={params}
                    href={route('places.showEdit', params.value)}                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxWidth: '30px',
                            maxHeight: '30px',
                            minWidth: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value}
                    </Button>
                </Link>
            )
        },
        {
            field: 'kid_id',
            headerName: 'Criança',
            flex: 1,
            renderCell: (params) => {
                if(params.value != null) {
                    return (
                        <Link
                            key={params.value.id}
                            href={route('kids.showEdit', params.value.id)}
                            className='text-blue-500'
                        >
                                {params.value.name}
                        </Link>
                    )
                } else return null
            }
        }
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Paragens do Pedido #{order.id}</h2>}
        >

            <Head title="Paragens do Pedido" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Table data={orderStopsInfo} columnsLabel={orderStopsLabels} dataId={'id'}/>

                    <CustomDataGrid 
                        rows={orderStopsInfo}
                        columns={orderStopsColumns}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}