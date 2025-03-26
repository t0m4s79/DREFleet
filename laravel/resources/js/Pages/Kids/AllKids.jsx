import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button, Snackbar, Alert } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import CustomDataGrid from '@/Components/CustomDataGrid';

export default function AllKids({ auth, kids, flash }) {

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
        const placesIds = kid.places.map((place) => place.id).join(', ');
    
        return {
            id: kid.id,
            name: kid.name,
            kid_contacts: kid.id, // It seems like you want to reference `kid.id` here; adjust if necessary
            wheelchair: kid.wheelchair, //? 'Sim' : 'Não',
            places_count: kid.places.length,
            place_ids: placesIds,
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

    const kidColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 60,
            hideable: false
        },
        {
            field: 'name',
            headerName: 'Nome',
            flex: 1,
            minWidth: 160,
        },
        {
            field: 'kid_contacts',
            headerName: 'Contactos',
            flex: 1,
            minWidth: 160,
            disableColumnMenu: true,
            disableExport: true,
            sortable: false,
            renderCell: (params) => (
                <Link
                    key={params.value}
                    href={route('kids.contacts', params.value)}
                >
                    <Button
                        variant="outlined"
                        sx={{
                            maxHeight: '30px',
                            minHeight: '30px',
                            margin: '0px 4px'
                        }}
                    >
                        Consultar
                    </Button>
                </Link>
            )
        },
        {
            field: 'wheelchair',
            headerName: 'Cadeira de Rodas',
            headerAlign: 'left',
            type: 'boolean',
            maxWidth: 150,
            flex: 1,
        },
        {
            field: 'places_count',
            headerName: 'Número de Moradas',
            type: 'number',
            headerAlign: 'left',
            align: 'center',
            //maxWidth: 150,
            flex: 1,
        },
        {
            field: 'place_ids',
            headerName: 'Moradas',
            flex: 1,
            minWidth: 160,
            disableColumnMenu: true,
            sortComparator: (a, b) => {
                const countA = a ? a.split(', ').length : 0;
                const countB = b ? b.split(', ').length : 0;
                return countA - countB;
            },
            renderCell: (params) => {
                const ids = params.value;

                if (ids.length > 0) {
                    return (
                        <div>
                            {ids.split(', ').map((id) => (
                                <Link key={id} href={route('places.showEdit', { id })}>
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
                                        {id}
                                    </Button>
                                </Link>
                            ))}
                        </div>
                    )
                } else {
                    return null;
                }
            }
        },
    ]
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crianças</h2>}
        >

            <Head title="Crianças" />
        

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div className='bg-sky-600'>
                        <Button href={route('kids.showCreate')}>
                            <AddIcon className='text-white' />
                            <a className="font-medium text-white dark:text-white hover:underline">
                                Nova Criança
                            </a>
                        </Button>
                    </div>

                    {/* <Table data={kidInfo} columnsLabel={kidColumnLabels} editAction="kids.showEdit" deleteAction="kids.delete" dataId="id"/> */}
                
                    <CustomDataGrid 
                        rows={kidInfo}
                        columns={kidColumns}
                        editAction="kids.showEdit"
                        deleteAction="kids.delete"
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