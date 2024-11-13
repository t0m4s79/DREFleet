import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { DataGrid } from '@mui/x-data-grid';
import CustomDataGrid from '@/Components/CustomDataGrid';


export default function AllPlaces( {auth, places, flash} ) {

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
    
    // Deconstruct data to send to table component
    const placeInfo = places.map((place) => {
        const kidPlacesIds = place.kids.length
            ? place.kids.map((kid) => ({ id: kid.id })) // Store kid id in a more structured way
            : [];

        const coordinates = `lat: ${place.coordinates.coordinates[1]}, lng: ${place.coordinates.coordinates[0]}`
      
        return {
            id: place.id,
            address: place.address,
            known_as: place.known_as,
            place_type: place.place_type,
            coordinates: coordinates,
            kids_count: place.kids.length,
            kids_ids: kidPlacesIds
        }
    })

    const placeColumnLabels = {
        id: 'ID',
        address: 'Morada',
        known_as: 'Conhecido como',
        place_type: 'Tipo',
        coordinates: 'Coordenadas',
        kids_count: 'Número de crianças',
        kids_ids: 'Crianças',
    };

    const placeColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'address',
            headerName: 'Morada',
            flex: 1,
        },
        {
            field: 'known_as',
            headerName: 'Conhecido como',
            flex: 1,
            maxWidth: 200,
        },
        {
            field: 'place_type',
            headerName: 'Tipo',
            flex: 1,
            maxWidth: 120,
        },
        {
            field: 'coordinates',
            headerName: 'Coordenadas',
            flex: 1,
        },
        {
            field: 'kids_count',
            headerName: 'Número de crianças',
            flex: 1,
            maxWidth: 150,
        },
        {
            field: 'kids_ids',
            headerName: 'Crianças',
            flex: 1,
            renderCell: (params) => (
                <div>
                    {params.value.map((kid) => (
                        <Link
                            key={kid.id}
                            href={route('kids.showEdit', kid)}
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
                                {kid.id}
                            </Button>
                        </Link>
                    ))}
                </div>
            )
        },
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Moradas</h2>}
        >

            <Head title="Moradas" />
        

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('places.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Morada
                        </a>
                    </Button>

                    <Table data={placeInfo} columnsLabel={placeColumnLabels} editAction="places.showEdit" deleteAction="places.delete" dataId="id"/>
                
                    <CustomDataGrid 
                        rows={placeInfo}
                        columns={placeColumns}
                        editAction="places.showEdit"
                        deleteAction="places.delete"
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