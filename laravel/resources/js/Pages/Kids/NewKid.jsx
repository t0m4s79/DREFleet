import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button, Checkbox, ListItemText, MenuItem, OutlinedInput, Select } from '@mui/material';
import { useState } from 'react';

export default function NewKid({auth, places}) {

    const [kidPlaces, setKidPlaces] = useState([])

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm
    ({
        wheelchair:'',
        name: '',
        phone: '',
        email: '',
        places: [],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    console.log('places', places)

    const placesList = places.map((place) => {
        return {value: place.id, label: `#${place.id} - ${place.address}`}
    })
    console.log('placesList', placesList)

    const handleChange = (event) => {
        const {
            target: { value },
        } = event;

        const selectedPlaces = typeof value === 'string' ? value.split(',') : value;

        setKidPlaces(selectedPlaces);
        setData('places', selectedPlaces);
    };

    console.log('kidPlaces', kidPlaces)

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('kids.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Criança</h2>}
        >
            <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>

                        <h2>Criar criança</h2>
                        <form onSubmit={handleSubmit} id="newKidForm">
                            <input type="hidden" name="_token" value={csrfToken} />

                            <label htmlFor="name">Nome</label><br/>
                            <input 
                                type="text" 
                                id="name" 
                                name="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                            /><br/>

                            <label htmlFor="email">Email do encarregado de educação</label><br/>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}    
                            /><br/>

                            <label htmlFor="phone">Número de telefone do encarregado de educação</label><br/>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}    
                            /><br/>
                            
                            <p>Utiliza cadeira de rodas?</p>
                            <input 
                                type="radio" 
                                name="wheelchair" 
                                value="1"
                                checked={data.wheelchair === '1'}
                                onChange={(e) => setData('wheelchair', e.target.value)}
                            />
                            <label>Sim</label><br/>
                            <input 
                                type="radio" 
                                name="wheelchair" 
                                value="0"
                                checked={data.wheelchair == '0'}
                                onChange={(e) => setData('wheelchair', e.target.value)}
                            />
                            <label>Não</label><br/>

                            <p>Adicionar Morada</p>
                            <Select 
                                multiple
                                value={kidPlaces}
                                onChange={handleChange}
                                input={<OutlinedInput label="Tag" />}
                                renderValue={(selected) => selected.join(', ')}
                                sx={{ maxHeight: '200px', width: '250px'}}
                            >
                                {placesList.map((place)=>(
                                    <MenuItem key={place.value} value={place.value}>
                                        <Checkbox checked={kidPlaces.indexOf(place.value) > -1} />
                                        <ListItemText primary={place.label} />
                                  </MenuItem>
                                ))}
                            </Select>
                            {/* <select name="places[]" id="places" multiple>
                                <option value="">-- Nenhuma Selecionada --</option>
                                    {}
                            </select> */}

                            <p><Button type="submit" value="Submit">Submeter</Button></p>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}
