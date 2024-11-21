import OrderRoutePolygon from '@/Components/OrderRoutePolygon'
import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import React, { useEffect, useState } from 'react'
import { parse } from 'date-fns';
import MapModal from '@/Components/MapModal';
import CustomDataGrid from '@/Components/CustomDataGrid';

export default function AllOrderRoutes({auth, orderRoutes, flash}) {
    //console.log(orderRoutes)
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

    const orderRoutesInfo = orderRoutes.map((orderRoute)=> {
        const orderArea = { area: JSON.stringify(orderRoute.area), color: orderRoute.area_color}

        return {
            id: orderRoute.id,
            name: orderRoute.name,
            drivers: orderRoute.drivers,
            technicians: orderRoute.technicians,
            orderArea,
            created_at: orderRoute.created_at,
            updated_at: orderRoute.updated_at
        }
    })

    const orderRoutesLabels = {
        id: 'ID',
        name: 'Rota',
        drivers: 'Condutores',
        technicians: 'Técnicos',
        orderArea: 'Área',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização'
    }

    const orderRoutesColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 80,
            hideable: false,
        },
        {
            field: 'name',
            headerName: 'Rota',
            flex: 1,
        },
        {
            field: 'drivers',
            headerName: 'Condutor',
            flex: 1,
            renderCell: (params) => (
                <div>
                    {params.value.map((driver) => (
                        <Link
                            key={driver.user_id}
                            href={route('drivers.showEdit', driver)}
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
                                {driver.user_id}
                            </Button>
                        </Link>
                    ))}
                </div>
            )
        },
        {
            field: 'technicians',
            headerName: 'Técnico',
            flex: 1,
            renderCell: (params) => (
                <div>
                    {params.value.map((tech) => (
                        <Link
                            key={tech.id}
                            href={route('technicians.showEdit', tech)}
                        >
                            <Button
                                //key={tech.id}
                                variant="outlined"
                                //href={route('technicians.showEdit', tech)}
                                sx={{
                                    maxWidth: '30px',
                                    maxHeight: '30px',
                                    minWidth: '30px',
                                    minHeight: '30px',
                                    margin: '0px 4px'
                                }}
                            >
                                {tech.id}
                            </Button>
                        </Link>
                    ))}
                </div>
            )
        },
        {
            field: 'orderArea',
            headerName: 'Área',
            flex: 1,
            disableExport: true,
            renderCell: (params) => (
                <MapModal route={params.value}/>
            )
        },
        {
            field: 'created_at',
            headerName: 'Data de Criação',
            type: 'dateTime',
            flex: 1,
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
            flex: 1,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Rotas</h2>}
        >

            {<Head title='Rotas' />}

            <div className='py-12 px-6'>
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('orderRoutes.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Rota
                        </a>
                    </Button>

                    <Table data={orderRoutesInfo} columnsLabel={orderRoutesLabels} editAction={'orderRoutes.showEdit'} deleteAction={'orderRoutes.delete'} dataId={'id'}/>

                    <CustomDataGrid
                        rows={orderRoutesInfo}
                        columns={orderRoutesColumns}
                        editAction={'orderRoutes.showEdit'}
                        deleteAction={'orderRoutes.delete'}
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
