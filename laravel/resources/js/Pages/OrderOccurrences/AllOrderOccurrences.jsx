import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import React, { useEffect, useState } from 'react'
import CustomDataGrid from '@/Components/CustomDataGrid';
import { parse } from 'date-fns';
import MouseHoverPopover from '@/Components/MouseHoverPopover';

export default function AllOrderOccurences({auth, occurrences, flash}) {

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

    const orderOccurrencesInfo = occurrences.map((occurrence)=> {          
        return {
            id: occurrence.id,
            date: occurrence.order.expected_begin_date,
            order_id: occurrence.order_id,
            driver_id: occurrence.order.driver,
            vehicle_id: occurrence.order.vehicle,
            type: occurrence.type,
            vehicle_towed: occurrence.vehicle_towed,
            description: occurrence.description,
            created_at: occurrence.created_at,
            updated_at: occurrence.updated_at
        }
    })

    const orderOccurrencesLabels = {
        id: 'ID',
        date: 'Data',
        order_id: 'Pedido',
        driver_id: 'Condutor',
        vehicle_id: 'Veículo',
        type: 'Tipo',
        vehicle_towed: 'Veículo rebocado',
        description: 'Descrição',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização'
    }

    const orderOccurrencesColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'date',
            headerName: 'Data',
            type: 'date',
            flex: 1,
            valueGetter: (params) => {
                const parsedDate = parse(params, 'dd-MM-yyyy', new Date());
                return parsedDate
            },
            
        },
        {
            field: 'order_id',
            headerName: 'Pedido',
            flex: 1,
            maxWidth: 100,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('orders.showEdit', params.value)}
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
        },
        {
            field: 'driver_id',
            headerName: 'Condutor',
            flex: 1,
            maxWidth: 100,
            renderCell: (params) => (
                <Link
                    key={params.value.user_id}
                    href={route('drivers.showEdit', params.value.user_id)}
                    className='text-blue-500'
                >
                        {params.value.name}
                </Link>
            )
        },
        {
            field: 'vehicle_id',
            headerName: 'Veículo',
            flex: 1,
            maxWidth: 100,
            renderCell: (params) => (
                <Link
                    key={params.value.id}
                    href={route('vehicles.showEdit', params.value.id)}
                    className='text-blue-500'
                >
                        {params.value.license_plate}
                </Link>
            )
        },
        {
            field: 'type',
            headerName: 'Tipo',
            flex: 1,
        },
        {
            field: 'vehicle_towed',
            headerName: 'Reboque Utilizado',
            type: 'boolean',
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Ocorrências</h2>}
        >

            {<Head title='Ocorrências' />}

            <div className='py-12 px-6'>
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('orderOccurrences.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Ocorrência
                        </a>
                    </Button>

                    {/* <Table data={orderOccurrencesInfo} columnsLabel={orderOccurrencesLabels} editAction={'orderOccurrences.showEdit'} deleteAction={'orderOccurrences.delete'} dataId={'id'}/> */}
                
                    <CustomDataGrid
                        rows={orderOccurrencesInfo}
                        columns={orderOccurrencesColumns}
                        editAction={'orderOccurrences.showEdit'}
                        deleteAction={'orderOccurrences.delete'}
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
