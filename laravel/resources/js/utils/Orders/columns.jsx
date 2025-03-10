import MapModal from "@/Components/MapModal";
import ObservationModal from "@/Components/ObservationModal";
import OccurrenceModal from "@/Components/OccurrenceModal";
import { Link } from "@inertiajs/react";
import { Button, Chip } from "@mui/material";
import { parse } from "date-fns";

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


const columns = [
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
        field: 'vehicle_license_plate',
        headerName: 'Veículo',
        //flex: 1,
        maxWidth: 100,
        renderCell: (params) => (
            <Link
                key={params.value}
                href={route('vehicles.showEdit', params.row.vehicle_id)}
                className='text-blue-500'
            >
                {params.row.vehicle_license_plate}
            </Link>
        )
    },
    {
        field: 'driver_name',
        headerName: 'Condutor',
        //flex: 1,
        minWidth: 100,
        renderCell: (params) => (
            <Link
                key={params.value}
                href={route('drivers.showEdit', params.row.driver_id)}
                className='text-blue-500'
            >
                {params.row.driver_name}
            </Link>
        )
    },
    {
        field: 'technician_name',
        headerName: 'Técnico',
        //flex: 1,
        minWidth: 100,
        renderCell: (params) => (
            <Link
                key={params.value}
                href={route('technicians.showEdit', params.row.technician_id)}
                className='text-blue-500'
            >
                {params.row.technician_name}
            </Link>
        )
    },
    {
        field: 'route',
        headerName: 'Rota',
        //flex: 1,
        renderCell: (params) => {
            if (params.value != '-') {
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
        field: 'observations',
        headerName: 'Observações',
        //flex: 1,
        renderCell: (params) => {
            // Only render the button if there are observations
            if (params.value) {
                return (
                    <div>
                        <ObservationModal observations={params.value} />
                    </div>
                );
            } else {
                return null; // Don't render anything if there are no occurrences
            }
        }
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
            <MapModal trajectory={params.value} />
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
        type: 'number',
        renderCell: (params) => `${(params.value / 1000).toFixed(3)} km`
    },
    {
        field: 'occurrences_count',
        headerName: 'Ocorrências',
        sortComparator: (a, b) => a - b,
        renderCell: (params) => {
            const occurrences = params.row?.occurrences || [];

            if (occurrences.length > 0) {
                return <OccurrenceModal occurrences={occurrences} link={params.row.id} />;
            }

            return null;
        }
    },
    {
        field: 'approved_date',
        headerName: 'Data de aprovação',
        type: 'dateTime',
        //flex: 1,
        valueGetter: (params) => {
            if (params != '-') {
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
            if (params.value == null) return params.value
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

const driverColumns = ['id', 'expected_begin_date', 'expected_end_date', 'driver_name', 'order_type', 'trajectory', 'observations', 'expected_time', 'distance', 'occurrences_count', 'staus']
const technicianColumns = ['id', 'expected_begin_date', 'expected_end_date', 'driver_name', 'technician_name', 'order_type', 'stops', 'trajectory', 'observations', 'expected_time', 'distance', 'occurrences_count', 'staus']

export const parseOrderColumns = (user) => {
    const filteredColumns = columns.filter((column) => {
        if (user.user_type === "Condutor") {
            return driverColumns.includes(column.field);
        } else if (user.user_type === "Técnico") {
            return technicianColumns.includes(column.field);
        } 
        return true;
    });

    if (user.user_type === "Condutor" || user.user_type === "Técnico") {
        return filteredColumns.map((column) => ({
            ...column,
            flex: 1
        }));
    }

    return filteredColumns;
};