import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Alert, Button, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import CustomDataGrid from '@/Components/CustomDataGrid';
import { parsePlacesColumns } from '@/utils/Places/columns';


export default function AllPlaces({ auth, places, flash }) {

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
        const kidIds = place.kids.map((kid) => kid.id).join(', ');

        const coordinates = `lat: ${place.coordinates.coordinates[1]}, lng: ${place.coordinates.coordinates[0]}`

        return {
            id: place.id,
            address: place.address,
            known_as: place.known_as,
            place_type: place.place_type,
            coordinates: coordinates,
            kids_count: place.kids.length,
            kids_ids: kidIds
        }
    })

    const placeColumns = parsePlacesColumns(auth.user)

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Moradas</h2>}
        >

            <Head title="Moradas" />


            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    {auth.user.user_type !== "Condutor" &&
                        <Button href={route('places.showCreate')}>
                            <AddIcon />
                            <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                Nova Morada
                            </a>
                        </Button>
                    }

                    <CustomDataGrid
                        rows={placeInfo}
                        columns={placeColumns}
                        editAction="places.showEdit"
                        deleteAction="places.delete"
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