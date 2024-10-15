import { Head } from '@inertiajs/react';

export default function EditKidEmail( {auth, kids, kidEmail} ) {

    console.log(kidEmail);
    console.log(kids);
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Informações de Email #{kidEmail.id}</h2>}
        >

            {<Head title='Editar Email de Criança' />}

 
        </AuthenticatedLayout>

    )
}