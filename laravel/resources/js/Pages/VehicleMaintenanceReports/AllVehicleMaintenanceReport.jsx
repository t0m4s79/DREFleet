import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar, Chip } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { parse } from 'date-fns';
import { WarningAmber } from '@mui/icons-material';
import CustomDataGrid from '@/Components/CustomDataGrid';
import MouseHoverPopover from '@/Components/MouseHoverPopover';
import MaintenanceMaterialsModal from '@/Components/MaintenanceMaterialsModal';

const renderMaintenanceStatus = (status) => {
    const colors = {
        'A decorrer': 'info',
        'Finalizado': 'success',
        'Agendado': 'warning',
    };

    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllVehicleMaintenanceReport({ auth, reports, flash }) {
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

    const dataReports = reports.map((report) => {

        let status;

        const beginDate = parse(report.begin_date, 'dd-MM-yyyy', new Date());
        const endDate = report.end_date ? parse(report.end_date, 'dd-MM-yyyy', new Date()) : null;
        const now = new Date();

        if (beginDate > now) {
            status = "Agendado";
        } else if (endDate && endDate < now) {
            status = "Finalizado";
        } else {
            status = "A decorrer";
        }

        return {
            id: report.id,
            begin_date: report.begin_date,
            end_date: report.end_date,
            vehicle_id: report.vehicle.id,
            vehicle_license_plate: report.vehicle.license_plate,
            type: report.type,
            description: report.description,
            kilometrage: report.kilometrage,
            total_cost: report.total_cost,
            items_cost: report.items_cost,
            service_provider: report.service_provider,
            status,
            created_at: report.created_at,
            updated_at: report.updated_at,
        }
    })

    const maintenanceColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'begin_date',
            headerName: 'Data de Início',
            type: 'date',
            minWidth: 120,
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
        },
        {
            field: 'end_date',
            headerName: 'Data de Fim',
            type: 'date',
            minWidth: 120,
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
        },
        {
            field: 'type',
            headerName: 'Tipo',
            flex: 1,
            minWidth: 100,
        },
        {
            field: 'description',
            headerName: 'Descrição',
            flex: 1,
            minWidth: 180,
            renderCell: (params) => (
                <MouseHoverPopover data={params.value} />
            )
        },
        {
            field: 'kilometrage',
            headerName: 'Kilometragem',
            flex: 1,
            minWidth: 100,
        },
        {
            field: 'total_cost',
            headerName: 'Custo Total',
            flex: 1,
            minWidth: 100,
            renderCell: (params) => {
                // If maintenance report have a list of items with cost, total cost is calculated based on that
                if (params.row.items_cost) {
                    const items = params.row.items_cost;

                    const total = Object.values(items)
                        .map(value => {
                            if (typeof value === "string") {
                                return parseFloat(value.replace(',', '.')) || 0;
                            }
                            return typeof value === "number" ? value : 0;
                        })
                        .reduce((acc, curr) => acc + curr, 0);

                    return total.toFixed(2);
                }

                return params.value
            }
        },
        {
            field: 'items_cost',
            headerName: 'Materiais',
            flex: 1,
            minWidth: 100,
            renderCell: (params) => {
                if (params.value) {
                    return (
                        <div>
                            <MaintenanceMaterialsModal items={params.value} />
                        </div>
                    );
                } else {
                    return null;
                }
            }
        },
        {
            field: 'service_provider',
            headerName: 'Prestador de serviço',
            flex: 1,
            minWidth: 100,
        },
        {
            field: 'status',
            headerName: 'Estado',
            flex: 1,
            minWidth: 100,
            renderCell: (params) => (renderMaintenanceStatus(params.value))
        },
        {
            field: 'vehicle_license_plate',
            headerName: 'Veículo',
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
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
            minWidth: 160,
            maxWidth: 180,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
        {
            field: 'updated_at',
            headerName: 'Data da Última Atualização',
            type: 'dateTime',
            flex: 1,
            minWidth: 160,
            maxWidth: 200,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy HH:mm:ss', new Date());
                return parsedDate
            },
        },
    ]

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registos de Manutenção</h2>}
        >

            {<Head title='Registos de Manutenção do Veículo' />}



            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div className='bg-sky-600'>
                        <Button href={route('vehicleMaintenanceReports.showCreate')}>
                            <AddIcon className='text-white' />
                            <a className="font-medium text-white dark:text-white hover:underline">
                                Novo Registo de Manutenção
                            </a>
                        </Button>
                    </div>

                    <CustomDataGrid
                        rows={dataReports}
                        columns={maintenanceColumns}
                        editAction="vehicleMaintenanceReports.showEdit"
                        deleteAction="vehicleMaintenanceReports.delete"
                        user={auth.user}
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