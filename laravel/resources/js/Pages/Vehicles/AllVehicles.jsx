import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Alert, Snackbar, Icon, Chip, Tooltip } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import { Accessible, Build, ReadMoreOutlined, Verified } from '@mui/icons-material';

function renderStatus(status) {
    const colors = {
        'Disponível': 'success',
        'Em Serviço': 'info',
        'Em manutenção': 'warning',
        'Indisponível': 'error',
        'Escondido': 'default',
    };
  
    return <Chip label={status} color={colors[status]} variant="outlined" size="small" />;
}

export default function AllVehicles( {auth, vehicles, flash}) {

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

    const vehicleInfo = vehicles.map((vehicle) => {
        //console.log(vehicle)
        return {
            id: vehicle.id,
            make: vehicle.make,
            model: vehicle.model,
            license_plate: vehicle.license_plate,
            year: vehicle.year,
            heavy_vehicle: vehicle.heavy_vehicle,// ? 'Sim' : 'Não',
            heavy_type: vehicle.heavy_type,
            wheelchair_adapted: vehicle.wheelchair_adapted,// ? 'Sim' : 'Não' ,
            wheelchair_certified: vehicle.wheelchair_certified,// ? 'Sim' : 'Não',
            tcc: vehicle.tcc,// ? 'Sim' : 'Não',
            yearly_allowed_tows: vehicle.yearly_allowed_tows,
            this_year_tow_counts: vehicle.this_year_tow_counts,
            capacity: vehicle.capacity,
            fuel_consumption: vehicle.fuel_consumption,
            status: vehicle.status,
            fuel_type: vehicle.fuel_type,
            current_month_fuel_requests: vehicle.current_month_fuel_requests,
            current_kilometrage: vehicle.current_kilometrage,
            vehicle_kilometrage_reports: vehicle.id,
            vehicle_maintenance_reports: vehicle.id,
            vehicle_accesories_docs: vehicle.id,
        }
    })

    const VehicleColumnLabels = {
        id: 'ID',
        make: 'Marca',
        model: 'Modelo',
        license_plate: 'Matrícula',
        year: 'Ano',
        heavy_vehicle: 'Veículo Pesado',
        heavy_type: 'Tipo de Pesado',
        wheelchair_adapted: 'Adapto a Cadeiras de Rodas',
        wheelchair_certified: 'Certificado para Cadeira de Rodas',
        tcc: 'TCC',
        yearly_allowed_tows: 'Reboques Anuais Permitidos',
        this_year_tow_counts: 'Reboques Anuais Utilizados',
        capacity: 'Capacidade',
        fuel_consumption: 'Consumo',
        status: 'Estado',
        fuel_type: 'Tipo de Combustível',
        current_month_fuel_requests: 'Pedidos Mensais de Reabastecimento',
        current_kilometrage: 'Kilometragem Atual',
        vehicle_kilometrage_reports: 'Registo de Kilometragem',
        vehicle_maintenance_reports: 'Registos de Manutenção',
        vehicle_accesories_docs: 'Documentos e Acessórios',
    };

    const VehicleColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'make',
            headerName: 'Marca',
            flex: 1,
        },
        {
            field: 'model',
            headerName: 'Modelo',
            flex: 1,
        },
        {
            field: 'license_plate',
            headerName: 'Matrícula',
            flex: 1,
        },
        {
            field: 'year',
            headerName: 'Ano',
            flex: 1,
        },
        {
            field: 'heavy_vehicle',
            headerName: 'Veículo Pesado',
            flex: 1,
            type: 'boolean',
        },
        {
            field: 'heavy_type',
            headerName: 'Tipo de Pesado',
            flex: 1,
            renderCell: (params)=> (
                params.value != '-'? <Chip label={params.value} variant="outlined" size="small"/> : '-'
            )
        },
        {
            field: 'wheelchair_adapted',
            headerName: 'Adaptado a Cadeiras de Rodas',
            description: 'Adaptado a Cadeiras de Rodas',
            disableColumnMenu: true,
            headerAlign: 'left',
            flex: 1,
            minWidth: 60,
            type: 'boolean',
            renderHeader: ()=> (
                <Tooltip title='Adaptado a Cadeiras de Rodas'>
                    <Accessible /> Adaptado a Cadeiras de Rodas
                </Tooltip>
            ),
        },
        {
            field: 'wheelchair_certified',
            headerName: 'Certificado para Cadeira de Rodas',
            description: 'Certificado para Cadeira de Rodas',
            disableColumnMenu: true,
            flex: 1,
            minWidth: 80,
            type: 'boolean',
            renderHeader: ()=> (
                <Tooltip title='Certificado para Cadeira de Rodas'>
                    <Verified/>
                    <Accessible/> Certificado para Cadeira de Rodas
                </Tooltip>
            ),
        },
        {
            field: 'tcc',
            headerName: 'TCC',
            flex: 1,
            type: 'boolean'
        },
        {
            field: 'yearly_allowed_tows',
            headerName: 'Reboques Anuais Permitidos',
            description: 'Reboques Anuais Permitidos',
            flex: 1,
        },
        {
            field: 'this_year_tow_counts',
            headerName: 'Reboques Anuais Utilizados',
            description: 'Reboques Anuais Utilizados',
            flex: 1,
        },
        {
            field: 'capacity',
            headerName: 'Passageiros',
            flex: 1,
        },
        {
            field: 'fuel_consumption',
            headerName: 'Consumo (l/100km)',
            flex: 1,
        },
        {
            field: 'status',
            headerName: 'Estado',
            flex: 1,
            renderCell: (params) => renderStatus(params.value)
        },
        {
            field: 'fuel_type',
            headerName: 'Tipo de combustível',
            description: 'Tipo de combustível',
            flex: 1,
        },
        {
            field: 'current_month_fuel_requests',
            headerName: 'Pedidos Mensais de Reabastecimento',
            description: 'Pedidos Mensais de Reabastecimento',
            disableColumnMenu: true,
            flex: 1,
        },
        {
            field: 'current_kilometrage',
            headerName: 'Kilometragem Atual',
            description: 'Kilometragem Atual',
            disableColumnMenu: true,
            flex: 1,
        },
        {
            field: 'vehicle_kilometrage_reports',
            headerName: 'Registo de Kilometragem',
            description: 'Registo de Kilometragem',
            sortable: false,
            disableColumnMenu: true,
            flex: 1,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('vehicles.kilometrageReports', params.value)}
                >
                    <ReadMoreOutlined color='primary'/>
                </Link>
            ),
        },
        {
            field: 'vehicle_maintenance_reports',
            headerName: 'Registo de Manutenção',
            description: 'Registo de Manutenção',
            sortable: false,
            disableColumnMenu: true,
            flex: 1,
            renderHeader: () => (
                <Tooltip title='Registo de Manutenção'>
                    <Build/> Registo de Manutenção
                </Tooltip>
            ),
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('vehicles.maintenanceReports', params.value)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxHeight: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        Consultar
                    </Button>
                </Link>
            )
        },
        {
            field: 'vehicle_accesories_docs',
            headerName: 'Documentos e Acessórios',
            description: 'Documentos e Acessórios',
            sortable: false,
            disableColumnMenu: true,
            flex: 1,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('vehicles.documentsAndAccessories', params.value)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxHeight: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        Consultar
                    </Button>
                </Link>
            )
        },
    ]

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículos</h2>}
        >

            <Head title="Veículos" />

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('vehicles.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Novo Veículo
                        </a>
                    </Button>

                    <Table
                        data={vehicleInfo}
                        columnsLabel={VehicleColumnLabels}
                        editAction="vehicles.showEdit"
                        deleteAction="vehicles.delete"
                        dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                    />
                    
                    <DataGrid 
                        rows={vehicleInfo}
                        columns={VehicleColumns}
                        density='compact'
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
    )
}
