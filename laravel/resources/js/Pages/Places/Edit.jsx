import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({auth, place, kids}) {

    // Initialize state with kid data
    const [formData, setFormData] = useState({
        address: place.address,
        known_as: place.known_as,
        latitude: place.latitude,
        longitude: place.longitude,
    });

    
    const kid = kids.map((kid)=>(
        <option value={kid.id}>{kid.id} - {kid.name}</option>
    ));

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: type === 'radio' ? (checked ? value : prevState[name]) : value,
        }));
    };
    

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Morada {place.id}</h2>}
        >

            {/*<Head title={'Condutor'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/places/edit/${place.id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken}/>
                            <input type="hidden" name="_method" value="PUT"/>

                            <label htmlFor="address">Nome</label><br/>
                            <input 
                                type="text" 
                                id="address" 
                                name="address" 
                                value={formData.address} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="email">Conhecido como</label><br/>
                            <input 
                                type="text" 
                                id="known_as" 
                                name="known_as" 
                                value={formData.known_as} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="phone">Latitude</label><br/>
                            <input 
                                type="text" 
                                id="latitude" 
                                name="latitude" 
                                value={formData.latitude} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="phone">Longitude</label><br/>
                            <input 
                                type="text" 
                                id="longitude" 
                                name="longitude" 
                                value={formData.longitude} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}