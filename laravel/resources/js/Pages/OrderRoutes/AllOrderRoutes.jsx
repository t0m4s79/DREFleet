import OrderRoutePolygon from '@/Components/OrderRoutePolygon'
import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';

import React, { useEffect, useState } from 'react'

export default function AllOrderRoutes({auth, orderRoutes, flash}) {

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
        return {id: orderRoute.id, name: orderRoute.name, area: JSON.stringify(orderRoute.area)}
    })

    const orderRoutesLabels = {
        id: 'ID',
        name: 'Rota',
        area: 'Área',
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Rota</h2>}
        >

            <div className='py-12 px-6'>
                <div className="bg-white overflow-hidden shadow-lg sm:rounded-lg">

                    <Button href={route('orderRoutes.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Rota
                        </a>
                    </Button>

                    <Table data={orderRoutesInfo} columnsLabel={orderRoutesLabels} editAction={'orderRoutes.showEditOrder'} deleteAction={'orderRoutes.delete'} dataId={'id'}/>
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
