import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function VehicleAccessoriesAndDocuments( {auth, vehicle} ) {

    console.log(vehicle);
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Documentos e Accessórios do Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Documentos e Acessórios de Veículo' />}

            {/*TODO: TABLES WITH DOCUMENTS AND ACCESSORIES */}
            <div className="py-12 px-6">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">


                    <Table data={[]} columnsLabel={[]} editAction={''} dataId="id"/>
                </div>
            </div>
 
        </AuthenticatedLayout>

    )
}