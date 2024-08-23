import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function AllPlaces( {auth, places} ) { //falta kids sem places para formulario de inserção

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    //Deconstruct data to send to table component
    let cols;
    let placeInfo = places.map((place) => (
        {id: place.id, address: place.address, known_as: place.known_as, latitude: place.latitude, longitude: place.longitude}
    ))

    if(places.length > 0){
        cols = Object.keys(placeInfo[0])
    }

    const placeColumnLabels = {
        id: 'ID',
        address: 'Morada',
        known_as: 'Conhecido como',
        latitude: 'Latitude',
        longitude: 'Longitude',
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Moradas</h2>}
        >

            <Head title="Moradas" />
        

            <div className='m-2 p-6'>

                {/* <a href={route('places.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Novo Condutor
                </a> */}

                {places && cols && <Table data={placeInfo} columns={cols} columnsLabel={placeColumnLabels} editAction="places.showEdit" deleteAction="places.delete" dataId="id"/> }

            </div>

            <h2>Criar morada</h2>
            <form action="places/create" method='POST' id="newPlaceForm">
                <input type="hidden" name="_token" value={csrfToken} />

                <label htmlFor="name">Morada</label><br/>
                <input type="text" id="address" name="address"/><br/>

                <label htmlFor="known_as">Conhecido como</label><br/>
                <input type="text" id="known_as" name="known_as"/><br/>

                <label htmlFor="latitude">Latitude</label><br/>
                <input type="number" step=".00001" id="latitude" name="latitude" placeholder="0.00000" min="-90" max="90"></input><br/>

                <label htmlFor="latitude">Longitude</label><br/>
                <input type="number" step=".00001" id="longitude" name="longitude" placeholder="0.00000" min="-180" max="180"></input><br/>

                <p><button type="submit" value="Submit">Submeter</button></p>
            </form>

        </AuthenticatedLayout>
    );
}