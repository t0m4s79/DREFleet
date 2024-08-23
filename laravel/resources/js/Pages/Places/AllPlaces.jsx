import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function AllPlaces( {auth, places} ) {

    //console.log('places', places)

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    //Deconstruct data to send to table component
    let cols;
    let kidPlacesIds;
    let placeInfo = places.map((place) => {
        if(place.kids.length){
            kidPlacesIds = place.kids.map((kid) => (
                <Button variant='outlined' href={route('kids.showEdit', kid)} sx={{maxWidth: '30px', maxHeight: '30px', minWidth: '30px', minHeight: '30px', margin: '0px 4px'}}>{kid.id}</Button>
            ))
        }
        else kidPlacesIds = '-'

        return {id: place.id, address: place.address, known_as: place.known_as, latitude: place.latitude, longitude: place.longitude, kids_count: place.kid_ids == [] ? 0: place.kid_ids.length, kids_ids: kidPlacesIds}
    })

    if(places.length > 0){
        cols = Object.keys(placeInfo[0])
    }

    const placeColumnLabels = {
        id: 'ID',
        address: 'Morada',
        known_as: 'Conhecido como',
        latitude: 'Latitude',
        longitude: 'Longitude',
        kids_count: 'Número de Crianças',
        kids_ids: 'Ids das Crianças',
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