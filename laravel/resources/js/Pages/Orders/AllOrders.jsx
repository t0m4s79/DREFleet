import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert, Chip } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import 'leaflet/dist/leaflet.css';
import Table from '@/Components/Table';
import { useEffect, useState } from 'react';
import { parse } from 'date-fns';
import CustomDataGrid from '@/Components/CustomDataGrid';
import OccurrenceModal from '@/Components/OccurrenceModal';
import MapModal from '@/Components/MapModal';

const renderOrderStatus = (status) => {
    const colors = {
        'Finalizado': 'success',
        'Aprovado': 'success',
        'Em curso': 'info',
        'Interrompido': 'warning',
        'Cancelado/Não aprovado': 'error',
        'Por aprovar': 'default',
    };
  
    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllOrders({auth, orders, flash}) {

    const [openSnackbar, setOpenSnackbar] = useState(false);                // defines if snackbar shows or not
    const [snackbarMessage, setSnackbarMessage] = useState('');             // defines the message to be shown in the snackbar
    const [snackbarSeverity, setSnackbarSeverity] = useState('success');    // 'success' or 'error'

    useEffect(() => {
        if (flash.message || flash.error) {                                 // if there is a flash message/error
            setSnackbarMessage(flash.message || flash.error);               // set the message
            setSnackbarSeverity(flash.error ? 'error' : 'success');         // defines background color of snackbar
            setOpenSnackbar(true);                                          // show snackbar
        }
    }, [flash]);

    const formatTime = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        // Pad the numbers with leading zeros if needed
        return `${String(hours).padStart(2, '0')}h${String(minutes).padStart(2, '0')}m`;
    };

    const OrderInfo = orders.map((order) => {
        //console.log(order)
        return {
            id: order.id,
            expected_begin_date: order.expected_begin_date,
            expected_end_date: order.expected_end_date,
            vehicle_id: order.vehicle,
            driver_id: order.driver,
            technician_id: order.technician,
            route: order.order_route_id,
            order_type: order.order_type,
            stops: order.order_stops.length,
            trajectory: order.trajectory,
            expected_time: formatTime(order.expected_time), // Convert expected time to hh:mm
            distance: (order.distance / 1000).toFixed(2) + 'km',
            occurrences: order.occurrences, // Number of occurrences
            approved_date: order.approved_date,
            approved_by: order.manager_id,
            status: order.status,
            created_at: order.created_at,
            updated_at: order.updated_at,
        };
    });

    const orderColumnLabels = {
        id: 'ID',
        expected_begin_date: 'Data de início',
        expected_end_date: 'Data de fim',
        vehicle_id: 'Veículo',
        driver_id: 'Condutor',
        technician_id: 'Técnico',
        route: 'Rota',
        order_type: 'Tipo',
        stops: 'Paragens',
        trajectory: 'Trajeto',
        expected_time: 'Tempo de Viagem Esperado',
        distance: 'Distância',
        occurrences: 'Ocorrências',
        approved_date: 'Data de aprovação',
        approved_by: 'Aprovado por',
        status: 'Estado',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização',
    }

    const orderColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'expected_begin_date',
            headerName: 'Data de início',
            type: 'dateTime',
            //flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm', new Date());
                return parsedDate
            },          
        },
        {
            field: 'expected_end_date',
            headerName: 'Data de fim',
            type: 'dateTime',
            //flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm', new Date());
                return parsedDate
            },
        },
        {
            field: 'vehicle_id',
            headerName: 'Veículo',
            //flex: 1,
            maxWidth: 100,
            valueFormatter: (value) => value.license_plate,
            renderCell: (params) => (
                <Link
                    key={params.value.id}
                    href={route('vehicles.showEdit', params.value.id)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value.license_plate}
                    </Button>
                </Link>
            )
        },
        {
            field: 'driver_id',
            headerName: 'Condutor',
            //flex: 1,
            minWidth: 100,
            valueFormatter: (value) => value.name,
            renderCell: (params) => (
                <Link
                    key={params.value.id}
                    href={route('drivers.showEdit', params.value.user_id)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value.name}
                    </Button>
                </Link>
            )
        },
        {
            field: 'technician_id',
            headerName: 'Técnico',
            //flex: 1,
            minWidth: 100,
            valueFormatter: (value) => value.name,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('technicians.showEdit', params.value.id)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value.name}
                    </Button>
                </Link>
            )
        },
        {
            field: 'route',
            headerName: 'Rota',
            //flex: 1,
            renderCell: (params) => {
                if(params.value != '-'){
                    return (
                        <Link
                            key={params.value}
                            href={route('orderRoutes.showEdit', params.value)}
                        >
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
                } else return null
            }
        },
        {
            field: 'order_type',
            headerName: 'Tipo',
            //flex: 1,
        },
        {
            field: 'stops',
            headerName: 'Paragens',
            //flex: 1,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('orders.stops', params.row.id)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxHeight: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        {params.value} Paragem(ns) {/* Display the number of occurrences */}
                    </Button>
                </Link>
            )
        },
        {
            field: 'trajectory',
            headerName: 'Trajeto',
            disableExport: true,
            //flex: 1,
            renderCell: (params) => (
                <MapModal trajectory={params.value}/>
            )
        },
        {
            field: 'expected_time',
            headerName: 'Tempo de Viagem Esperado',
            //flex: 1,
        },
        {
            field: 'distance',
            headerName: 'Distância',
            //flex: 1,
        },
        {
            field: 'occurrences',
            headerName: 'Ocorrências',
            //flex: 1,
            valueFormatter: (value) => (value.map((elem)=> (`${elem.type}:${elem.description}`))),
            renderCell: (params) => {
                const occurences = params.value
                // Only render the button if there are occurrences
                if(occurences.length > 0){
                    return (
                        <div>
                            <OccurrenceModal occurences={occurences} link={params.row.id}/>
                        </div>
                    );
                } else {
                    return null; // Don't render anything if there are no occurrences
                }
            }
        },
        {
            field: 'approved_date',
            headerName: 'Data de aprovação',
            type: 'dateTime',
            //flex: 1,
            valueGetter: (params) => {
                if(params != '-'){
                    const parsedDate = parse(params, 'dd-MM-yyyy HH:mm', new Date());
                    return parsedDate
                } else return null
            },
        },
        {
            field: 'approved_by',
            headerName: 'Aprovado por',
            //flex: 1,
            renderCell: (params) => {
                if(params.value==null) return params.value
                return (
                    <Link
                        key={params.value}
                        href={route('managers.showEdit', params.value)}
                    >
                        <Button
                            variant="outlined"
                            sx={{
                                maxHeight: '30px',
                                minWidth: '30px',
                                margin: '0px 4px'
                            }}
                        >
                            {params.value}
                        </Button>
                    </Link>
                )
            },
        },
        {
            field: 'status',
            headerName: 'Estado',
            renderCell: (params) => (renderOrderStatus(params.value))
            //flex: 1,
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            //flex: 1,
            //maxWidth: 180,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
        {
            field: 'updated_at',
            headerName: 'Data da Última Atualização',
            type: 'dateTime',
            //flex: 1,
            //maxWidth: 200,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
    ]

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedidos</h2>}
        >

            <Head title="Pedidos" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('orders.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Pedido
                        </a>
                    </Button>

                    {/* <Table data={OrderInfo} columnsLabel={orderColumnLabels} editAction={'orders.edit'} deleteAction={'orders.delete'} dataId={'id'}/> */}
                
                    <CustomDataGrid
                        rows={OrderInfo}
                        columns={orderColumns}
                        editAction={'orders.edit'}
                        deleteAction={'orders.delete'}
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