import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({auth, kid, availablePlaces}) {

    console.log(kid)

    // Initialize state with kid data
    const [formData, setFormData] = useState({
        name: kid.name,
        email: kid.email,
        phone: kid.phone,
        wheelchair: kid.wheelchair,
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: type === 'radio' ? (checked ? value : prevState[name]) : value,
        }));
    };

    const addPlaces = availablePlaces.map((availablePlace)=>(
        <option key={availablePlace.id} value={availablePlace.id}>{availablePlace.id} - {availablePlace.address}</option>
    ));

    const removePlaces = kid.places.map((availablePlace)=>(
        <option key={availablePlace.id} value={availablePlace.id}>{availablePlace.id} - {availablePlace.address}</option>
    ));    

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Criança {kid.id}</h2>}
        >

            {/*<Head title={'Condutor'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/kids/edit/${kid.id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />

                            <label htmlFor="name">Nome</label><br/>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value={formData.name} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="email">Email</label><br/>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value={formData.email} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="phone">Número de telemóvel</label><br/>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value={formData.phone} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <p>Utiliza cadeira de rodas?</p>
                            <input 
                                type="radio" 
                                id="wheelchair_no" 
                                name="wheelchair" 
                                value="0" 
                                checked={formData.wheelchair == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="heavy_vehicle_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="wheelchair_yes" 
                                name="wheelchair" 
                                value="1" 
                                checked={formData.wheelchair == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="wheelchair_yes">Sim</label><br/>

                            <p>Adicionar Morada</p>
                            <select name="addPlaces[]" id="addPlaces" multiple>
                                <option value="">-- Nenhuma Selecionada --</option>
                                {addPlaces}
                            </select>

                            <p>Retirar Morada</p>    
                            <select name="removePlaces[]" id="removePlaces" multiple>
                                <option>-- Nenhuma Selecionada --</option>
                                {removePlaces}
                            </select>                    

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}