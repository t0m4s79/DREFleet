import { Head } from '@inertiajs/react';

export default function VehicleAccessoriesAndDocuments( {auth, kid} ) {

    console.log(kid);
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Contactos da Criança #{kid.id}</h2>}
        >

            {<Head title='Contactos da Criança' />}

            {/*TODO: TABLES WITH DOCUMENTS AND ACCESSORIES */}
 
        </AuthenticatedLayout>

    )
}