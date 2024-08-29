import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';


export default function AllPlaces( {auth, places, flash} ) {

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
    
    //Deconstruct data to send to table component
    let kidPlacesIds;
    let placeInfo = places.map((place) => {
        if(place.kids.length){
            kidPlacesIds = place.kids.map((kid) => (
                <Button variant='outlined' href={route('kids.showEdit', kid)} sx={{maxWidth: '30px', maxHeight: '30px', minWidth: '30px', minHeight: '30px', margin: '0px 4px'}}>{kid.id}</Button>
            ))
        }
        else kidPlacesIds = '-'

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
        

            <div className='m-2 p-6'>

                <Button href={route('places.create')}>
                    <AddIcon />
                    <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        Nova Morada
                    </a>
                </Button>

                <Table data={placeInfo} columnsLabel={placeColumnLabels} editAction="places.showEdit" deleteAction="places.delete" dataId="id"/>

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