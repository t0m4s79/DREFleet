import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';

export default function AllKids( {auth, kids, places, flash} ) {

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
    let cols;

    let kidPlacesIds;

    let kidInfo = kids.map((kid) => {

        if(kid.place_ids.length) {
            kidPlacesIds = kid.place_ids.map((place) => (
                <Button variant='outlined' href={route('places.showEdit', place)} sx={{maxWidth: '30px', maxHeight: '30px', minWidth: '30px', minHeight: '30px', margin: '0px 4px'}}>{place}</Button>
            ))
        } else kidPlacesIds = '-'

        return {id: kid.id, name: kid.name, email: kid.email, phone: kid.phone, wheelchair: kid.wheelchair, places_count: kid.place_ids.length, place_ids: kidPlacesIds }
    })

    if(kids.length > 0){
        cols = Object.keys(kidInfo[0])
    }

    const kidColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        wheelchair: 'Cadeira de Rodas',
        places_count: 'Número de Moradas',
        place_ids: 'Ids das Moradas'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crianças</h2>}
        >

            <Head title="Crianças" />
        

            <div className='m-2 p-6'>

                <Button href={route('kids.create')}>
                    <AddIcon />
                    <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        Nova Criança
                    </a>
                </Button>

                {kids && cols && <Table data={kidInfo} columns={cols} columnsLabel={kidColumnLabels} editAction="kids.showEdit" deleteAction="kids.delete" dataId="id"/> }

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