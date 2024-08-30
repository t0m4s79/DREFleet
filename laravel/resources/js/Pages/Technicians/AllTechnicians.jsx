import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import AddIcon from '@mui/icons-material/Add';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function AllTechnicians( {auth, technicians} ) {
    
    console.log(technicians)
    //Deconstruct data to send to table component
    let technicianInfo = technicians.map((technician) => (
        {id: technician.id, name: technician.name, email: technician.email, phone: technician.phone, status: technician.status }
    ))

    const technicianColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        status: 'Estado',
        kidsList1: 'Crianças de Prioridade 1',
        kidsList2: 'Crianças de Prioridade 2',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Técnicos</h2>}
        >

            <Head title="Utilizadores" />
        
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <Button href={route('technicians.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <AddIcon />
                        <a className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                            Novo Técnico
                        </a>
                    </Button>

                    <Table data={technicianInfo} columnsLabel={technicianColumnLabels} editAction="technicians.showEdit" deleteAction="technicians.delete" dataId="id"/>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}