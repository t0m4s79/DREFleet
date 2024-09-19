import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';


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
      
        return {id: place.id, address: place.address, known_as: place.known_as, latitude: place.coordinates.coordinates[0], longitude: place.coordinates.coordinates[1], kids_count: place.kid_ids == [] ? 0: place.kid_ids.length, kids_ids: kidPlacesIds}
    })

    const placeColumnLabels = {
        id: 'ID',
        address: 'Morada',
        known_as: 'Conhecido como',
        latitude: 'Latitude',
        longitude: 'Longitude',
        kids_count: 'Número de Crianças',
        kids_ids: 'Ids das Crianças',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Moradas</h2>}
        >

            <Head title="Moradas" />
        

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('places.create')}>
                        <AddIcon />
                        <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                            Nova Morada
                        </a>
                    </Button>

                    <Table data={placeInfo} columnsLabel={placeColumnLabels} editAction="places.showEdit" deleteAction="places.delete" dataId="id"/>
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