import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function AllDrivers( {auth, users} ) {
    
    //Deconstruct data to send to table component
    let userInfo = users.map((user) => (
        {id: user.id, name: user.name, email: user.email, phone: user.phone, user_type: user.user_type , status: user.status }
    ))

    const userColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        user_type: 'Tipo de Utilizador',
        status: 'Estado'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Utilizadores</h2>}
        >

            <Head title="Utilizadores" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('users.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <AddIcon />
                        <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                            Novo Utilizador
                        </a>
                    </Button>

                    <Table data={userInfo} columnsLabel={userColumnLabels} editAction="users.showEdit" deleteAction="users.delete" dataId="id"/>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}