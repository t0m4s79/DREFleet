import { Head } from '@inertiajs/react';
import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function KidContacts( {auth, kid} ) {

    console.log(kid);
    //Deconstruct data to display on "table"
    const kidEmails = kid.emails.map((email) => {
        return {
            id: email.id
        }
    })

    const EmailCols = {
        id: 'ID',
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Contactos da Criança #{kid.id}</h2>}
        >

            {<Head title='Contactos da Criança' />}

            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            {/*TODO: TABLES WITH DOCUMENTS AND ACCESSORIES */}
                    <Table data={kidEmails} columnsLabel={EmailCols} editAction="" deleteAction="" dataId="id"/>
                </div>
            </div>
 
        </AuthenticatedLayout>

    )
}