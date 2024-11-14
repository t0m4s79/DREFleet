import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button, Alert, Snackbar } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import { useEffect, useState } from 'react';
import { parse } from 'date-fns';
import CustomDataGrid from '@/Components/CustomDataGrid';

export default function KidContacts( {auth, kid, flash} ) {

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

    //Deconstruct data to display on "table"
    const kidEmails = kid.emails.map((kidEmail) => {
        return {
            id: kidEmail.id,
            email: kidEmail.email,
            owner_name: kidEmail.owner_name,
            relationship_to_kid: kidEmail.relationship_to_kid,
            preference: kidEmail.preference,
            created_at: kidEmail.created_at,
            updated_at: kidEmail.updated_at,
        }
    })

    const kidEmailsColLabels = {
        id: 'ID',
        email: 'Email',
        owner_name: 'Nome',
        relationship_to_kid: 'Parentesco',
        preference: 'Forma de Contacto',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização',
    }

    const kidEmailColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 100,
            hideable: false
        },
        {
            field: 'email',
            headerName: 'Email',
            flex: 1,
        },
        {
            field: 'owner_name',
            headerName: 'Nome',
            flex: 1,
        },
        {
            field: 'relationship_to_kid',
            headerName: 'Parentesco',
            flex: 1,
        },
        {
            field: 'preference',
            headerName: 'Forma de Contacto',
            flex: 1,
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

    //Deconstruct data to display on "table"
    const kidPhones = kid.phone_numbers.map((phoneNumber) => {
        return {
            id: phoneNumber.id,
            phone: phoneNumber.phone,
            owner_name: phoneNumber.owner_name,
            relationship_to_kid: phoneNumber.relationship_to_kid,
            preference: phoneNumber.preference,
            created_at: phoneNumber.created_at,
            updated_at: phoneNumber.updated_at,
        }
    })

    const kidPhonesColLabels = {
        id: 'ID',
        phone: 'Número de Telemóvel',
        owner_name: 'Nome',
        relationship_to_kid: 'Parentesco',
        preference: 'Forma de Contacto',
        created_at: 'Data de criação',
        updated_at: 'Data da última atualização',
    }

    const kidPhonesColumns = [
        {
            field: 'id',
            headerName: 'ID',
            flex: 1,
            maxWidth: 100,
            hideable: false
        },
        {
            field: 'phone',
            headerName: 'Número de Telemóvel',
            flex: 1,
        },
        {
            field: 'owner_name',
            headerName: 'Nome',
            flex: 1,
        },
        {
            field: 'relationship_to_kid',
            headerName: 'Parentesco',
            flex: 1,
        },
        {
            field: 'preference',
            headerName: 'Forma de Contacto',
            flex: 1,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Contactos da Criança #{kid.id}</h2>}
        >

            {<Head title='Contactos da Criança' />}

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <Button href={route('kidPhoneNumbers.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Número de Telemóvel
                                </a>
                            </Button>

                            <Table
                                data={kidPhones}
                                columnsLabel={kidPhonesColLabels}
                                editAction="kidPhoneNumbers.showEdit"
                                deleteAction="kidPhoneNumbers.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />

                            <CustomDataGrid 
                                rows={kidPhones}
                                columns={kidPhonesColumns}
                                editAction="kidPhoneNumbers.showEdit"
                                deleteAction="kidPhoneNumbers.delete"
                            />
                        </div>
                    </div>

                    <div className="py-12 px-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                            <Button href={route('kidEmails.showCreate')}>
                                <AddIcon />
                                <a className="font-medium text-sky-600 dark:text-sky-500 hover:underline">
                                    Novo Email
                                </a>
                            </Button>

                            <Table
                                data={kidEmails}
                                columnsLabel={kidEmailsColLabels}
                                editAction="kidEmails.showEdit"
                                deleteAction="kidEmails.delete"
                                dataId="id" // Ensure the correct field is passed for DataGrid's `id`
                            />
                            
                            <CustomDataGrid
                                rows={kidEmails}
                                columns={kidEmailColumns}
                                editAction="kidEmails.showEdit"
                                deleteAction="kidEmails.delete"
                            />
                        </div>
                    </div>

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