import { Head } from '@inertiajs/react';

export default function NewKidEmail( {auth, kids} ) {

    console.log(kids);
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Número de Telemóvel de Criança</h2>}
        >

            {<Head title='Criar Número de Telemóvel de Criança' />}

 
        </AuthenticatedLayout>

    )
}