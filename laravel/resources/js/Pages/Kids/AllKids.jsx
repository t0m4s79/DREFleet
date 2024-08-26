import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function AllKids( {auth, kids, places} ) {

    console.log('kids', places)

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    //Deconstruct data to send to table component
    let cols;

    let kidPlacesIds;

    let kidInfo = kids.map((kid) => {

        kidPlacesIds = kid.place_ids.map((place) => (
            <Button variant='outlined' href={route('places.showEdit', place)} sx={{maxWidth: '30px', maxHeight: '30px', minWidth: '30px', minHeight: '30px', margin: '0px 4px'}}>{place}</Button>
        ))
        return {id: kid.id, name: kid.name, email: kid.email, phone: kid.phone, wheelchair: kid.wheelchair, places_count: kid.place_ids.length, place_ids: kidPlacesIds }
    })

    const place = places.map((place)=>(
        <option key={place.id} value={place.id}>{place.id} - {place.address}</option>
    ));

    if(kids.length > 0){
        cols = Object.keys(kidInfo[0])
    }

    const kidColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        wheelchair: 'Cadeira de Rodas',
        places_count: 'Número de Moradas',
        place_ids: 'Ids das Moradas'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crianças</h2>}
        >

            <Head title="Crianças" />
        

            <div className='m-2 p-6'>

                <a href={route('kids.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Nova Criança
                </a>

                {kids && cols && <Table data={kidInfo} columns={cols} columnsLabel={kidColumnLabels} editAction="kids.showEdit" deleteAction="kids.delete" dataId="id"/> }

            </div>


        </AuthenticatedLayout>
    );
}