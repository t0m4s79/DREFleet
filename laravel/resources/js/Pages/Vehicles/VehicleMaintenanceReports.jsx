import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { parse } from 'date-fns';
import MouseHoverPopover from '@/Components/MouseHoverPopover';
import CustomDataGrid from '@/Components/CustomDataGrid';


export default function VehicleMaintenanceReports( {auth, vehicle, flash} ) {

    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    //Deconstruct props to send to Table
    const vehicleReports = vehicle.maintenance_reports.map((report) => {
        // Parse the JSON data field
        const additionalData = report.items_cost
            ? Object.entries(report.items_cost).map(([key, value]) => `${key}: ${value}`).join("\n")
            : [];
            
        return {
            id: report.id,
            begin_date: report.begin_date,
            end_date: report.end_date,
            type: report.type,
            description: report.description,
            kilometrage: report.kilometrage,
            total_cost: report.total_cost,
            items_cost: additionalData,
            service_provider: report.service_provider,
            status: report.status,
            vehicle_id: report.vehicle_id,
            created_at: report.created_at,
            updated_at: report.updated_at,
        }
    });

    const vehicleReportsColumnLabels = {
        id: 'ID',
        begin_date: 'Data de Início',
        end_date: 'Data de Fim',
        type: 'Tipo',
        description: 'Descrição',
        kilometrage: 'Kilometragem',
        total_cost: 'Custo Total',
        items_cost: 'Materiais',
        service_provider: 'Providenciador de Serviços',
        status: 'Estado',
        vehicle_id: 'Veículo',
        created_at: 'Data de Criação',
        updated_at: 'Data da Última Atualização',
    };

    const vehicleMaintenanceColumns = [
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
        },
        {
            field: 'description',
            headerName: 'Descrição',
            flex: 1,
            renderCell: (params) => (
                <MouseHoverPopover data={params.value} />
            )
        },
        {
            field: 'kilometrage',
            headerName: 'Kilometragem',
            flex: 1,
        },
        {
            field: 'total_cost',
            headerName: 'Custo Total',
            flex: 1,
        },
        {
            field: 'items_cost',
            headerName: 'Materiais',
            flex: 1,
        },
        {
            field: 'service_provider',
            headerName: 'Prestador de serviço',
            flex: 1,
        },
        {
            field: 'status',
            headerName: 'Estado',
            flex: 1,
        },
        {
            field: 'vehicle_id',
            headerName: 'Veículo',
            flex: 1,
            disableColumnMenu: true,
            sortable: false,
            maxWidth: 100,
            renderCell: (params) => (
                <Link href={route('vehicles.showEdit', params.value)}>
                    <Button >{vehicle.license_plate}</Button>
                </Link>
            )
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Registo de Manutenção do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Registo de Manutenção do Veículo' />}

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                            <Button href={route('vehicleMaintenanceReports.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Registo
                                </a>
                            </Button>

                            <Table
                                data={vehicleReports}
                                columnsLabel={vehicleReportsColumnLabels}
                                editAction="vehicleMaintenanceReports.showEdit"
                                deleteAction="vehicleMaintenanceReports.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />

                            <CustomDataGrid
                                rows={vehicleReports}
                                columns={vehicleMaintenanceColumns}
                                editAction="vehicleMaintenanceReports.showEdit"
                                deleteAction="vehicleMaintenanceReports.delete"
                            />
                        </div>
                    </div>
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

    )
}