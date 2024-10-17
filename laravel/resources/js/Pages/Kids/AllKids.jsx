import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function AllKids( {auth, kids, flash} ) {

    console.log(kids)
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
    const kidInfo = kids.map((kid) => {
        const kidPlacesIds = kid.places.length
            ? kid.places.map((place) => ({ id: place.id })) // Accessing the `id` property of each place
            : [];
    
        return {
            id: kid.id,
            name: kid.name,
            kid_contacts: kid.id, // It seems like you want to reference `kid.id` here; adjust if necessary
            wheelchair: kid.wheelchair ? 'Sim' : 'Não',
            places_count: kid.places.length,
            place_ids: kidPlacesIds,
        };
    });

    const kidColumnLabels = {
        id: 'ID',
        name: 'Nome',
        kid_contacts: 'Contactos',
        wheelchair: 'Cadeira de Rodas',
        places_count: 'Número de Moradas',
        place_ids: 'Moradas',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crianças</h2>}
        >

            <Head title="Crianças" />
        

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <Button href={route('kids.showCreate')}>
                        <AddIcon />
                        <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                            Nova Criança
                        </a>
                    </Button>

                    <Table data={kidInfo} columnsLabel={kidColumnLabels} editAction="kids.showEdit" deleteAction="kids.delete" dataId="id"/>
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