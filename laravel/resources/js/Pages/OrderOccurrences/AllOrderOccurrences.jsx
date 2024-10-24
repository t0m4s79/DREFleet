import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { Head } from '@inertiajs/react';
import React, { useEffect, useState } from 'react'

export default function AllOrderOccurences({auth, occurrences, flash}) {
    console.log(occurrences)
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
            driver_id: occurrence.order.driver.user_id,
            vehicle_id: occurrence.order.vehicle.id,
            type: occurrence.type,
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
        description: 'Descrição',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização'
    }

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

                    <Table data={orderOccurrencesInfo} columnsLabel={orderOccurrencesLabels} editAction={'orderOccurrences.showEdit'} deleteAction={'orderOccurrences.delete'} dataId={'id'}/>
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
