import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Button } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';

export default function KidContacts( {auth, kid} ) {

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
                        </div>
                    </div>

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
                        </div>
                    </div>

                </div>
            </div>
 
        </AuthenticatedLayout>

    )
}