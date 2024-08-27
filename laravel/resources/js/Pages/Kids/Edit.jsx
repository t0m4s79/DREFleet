import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button, Checkbox, FormControl, InputLabel, ListItemText, MenuItem, OutlinedInput, Select } from '@mui/material';
import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';

export default function Edit({auth, kid, availablePlaces}) {

    const [selectedAddPlaces, setSelectedAddPlaces] = useState([]);
    const [selectedRemovePlaces, setSelectedRemovePlaces] = useState([]);
    console.log(kid)

    const { data, setData, put, errors, processing } = useForm({
        name: kid.name,
        email: kid.email,
        phone: kid.phone,
        wheelchair: kid.wheelchair,
        addPlaces: [],
        removePlaces: [],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
    };

    const handleAddPlacesChange = (event) => {
        setSelectedAddPlaces(event.target.value);
        setData('addPlaces', selectedAddPlaces);

    };
    
    const handleRemovePlacesChange = (event) => {
        setSelectedRemovePlaces(event.target.value);
        setData('removePlaces', selectedRemovePlaces);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('kids.edit', kid.id));
    };

    // const addPlaces = availablePlaces.map((availablePlace)=>(
    //     <option key={availablePlace.id} value={availablePlace.id}>{availablePlace.id} - {availablePlace.address}</option>
    // ));

    // const removePlaces = kid.places.map((availablePlace)=>(
    //     <option key={availablePlace.id} value={availablePlace.id}>{availablePlace.id} - {availablePlace.address}</option>
    // ));    

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Criança {kid.id}</h2>}
        >

            {/*<Head title={'Condutor'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />

                            <label htmlFor="name">Nome</label><br/>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value={data.name} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            />
                            {errors.name && <InputError message={errors.name} />}
                            <br/>

                            <label htmlFor="email">Email</label><br/>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value={data.email} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            />
                            {errors.email && <InputError message={errors.email} />}
                            <br/>

                            <label htmlFor="phone">Número de telemóvel</label><br/>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value={data.phone} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            />
                            {errors.phone && <InputError message={errors.phone} />}
                            <br/>

                            <p>Utiliza cadeira de rodas?</p>
                            <input 
                                type="radio" 
                                id="wheelchair_no" 
                                name="wheelchair" 
                                value="0" 
                                checked={data.wheelchair == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="wheelchair_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="wheelchair_yes" 
                                name="wheelchair" 
                                value="1" 
                                checked={data.wheelchair == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="wheelchair_yes">Sim</label>
                            {errors.wheelchair && <InputError message={errors.wheelchair} />}
                            <br/>

                            <p>Adicionar Morada</p>
                            <FormControl sx={{ m: 1, minWidth: 300 }}>
                                <InputLabel id="add-places-label">Adicionar Morada</InputLabel>
                                <Select
                                    labelId="add-places-label"
                                    id="addPlaces"
                                    multiple
                                    value={selectedAddPlaces}
                                    onChange={handleAddPlacesChange}
                                    input={<OutlinedInput label="Adicionar Morada" />}
                                    renderValue={(selected) => selected.join(', ')}
                                >
                                    {availablePlaces.map((place) => (
                                        <MenuItem key={place.id} value={place.id}>
                                            <Checkbox checked={selectedAddPlaces.indexOf(place.id) > -1} />
                                            <ListItemText primary={`#${place.id} - ${place.address}`} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <p>Retirar Morada</p>
                            <FormControl sx={{ m: 1, minWidth: 300 }}>
                                <InputLabel id="remove-places-label">Retirar Morada</InputLabel>
                                <Select
                                    labelId="remove-places-label"
                                    id="removePlaces"
                                    multiple
                                    value={selectedRemovePlaces}
                                    onChange={handleRemovePlacesChange}
                                    input={<OutlinedInput label="Retirar Morada" />}
                                    renderValue={(selected) => selected.join(', ')}
                                >
                                    {kid.places.map((place) => (
                                        <MenuItem key={place.id} value={place.id}>
                                            <Checkbox checked={selectedRemovePlaces.indexOf(place.id) > -1} />
                                            <ListItemText primary={`#${place.id} - ${place.address}`} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                            <br/>

                            <Button type="submit" variant="outlined" disabled={processing}>Submeter</Button>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}