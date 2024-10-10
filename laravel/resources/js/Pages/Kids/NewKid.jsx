import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { TextField, Button, Checkbox, ListItemText, MenuItem, OutlinedInput, Select, FormControl, InputLabel, Radio, RadioGroup, FormControlLabel, FormLabel } from '@mui/material';import { useState } from 'react';

export default function NewKid({auth, places}) {

    const [kidPlaces, setKidPlaces] = useState([])

    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, post, errors, processing } = useForm
    ({
        wheelchair:'',
        name: '',
        phone: '',
        email: '',
        places: [],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Change how data is shown in the options
    const placesList = places.map((place) => {
        return {value: place.id, label: `#${place.id} - ${place.address}`}
    })

    // Handle AutoComplete changes when a place is selected
    const handleChange = (event) => {
        const {
            target: { value },
        } = event;

        const selectedPlaces = typeof value === 'string' ? value.split(',') : value;

        setKidPlaces(selectedPlaces);
        setData('places', selectedPlaces);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('kids.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Criança</h2>}
        >
        
            {<Head title='Criar Criança' />}

            <div className='py-12'>
            <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className='p-6'>

                        <h2>Criar criança</h2>
                        <form onSubmit={handleSubmit} id="newKidForm">
                            <input type="hidden" name="_token" value={csrfToken} />

                            <TextField
                                    fullWidth
                                    margin="normal"
                                    id="name"
                                    name="name"
                                    label="Nome"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={Boolean(errors.name)}
                                    helperText={errors.name}
                                />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="email"
                                    name="email"
                                    label="Email do encarregado de educação"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    error={Boolean(errors.email)}
                                    helperText={errors.email}
                                />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="phone"
                                    name="phone"
                                    label="Número de telefone do encarregado de educação"
                                    type="tel"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    error={Boolean(errors.phone)}
                                    helperText={errors.phone}
                                />

                                <FormControl component="fieldset" margin="normal">
                                    <FormLabel component="legend">Utiliza cadeira de rodas?</FormLabel>
                                    <RadioGroup
                                        row
                                        aria-label="wheelchair"
                                        name="wheelchair"
                                        value={data.wheelchair}
                                        onChange={(e) => setData('wheelchair', e.target.value)}
                                    >
                                        <FormControlLabel
                                            value="1"
                                            control={<Radio />}
                                            label="Sim"
                                        />
                                        <FormControlLabel
                                            value="0"
                                            control={<Radio />}
                                            label="Não"
                                        />
                                    </RadioGroup>
                                    {errors.wheelchair && <InputError message={errors.wheelchair} />}
                                </FormControl>
                                <br />
                                
                                <p>Adicionar Morada</p>
                                <FormControl sx={{ minWidth: 300 }} margin="normal">
                                    <InputLabel id="places-label">Adicionar Morada</InputLabel>
                                    <Select
                                        labelId="places-label"
                                        multiple
                                        value={kidPlaces}
                                        onChange={handleChange}
                                        input={<OutlinedInput label="Adicionar Morada" />}
                                        renderValue={(selected) => selected.join(', ')}
                                        sx={{ maxHeight: '200px', width: '100%' }}
                                    >
                                        {placesList.map((place) => (
                                            <MenuItem key={place.value} value={place.value}>
                                                <Checkbox checked={kidPlaces.indexOf(place.value) > -1} />
                                                <ListItemText primary={place.label} />
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                                <br/>

                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
            
        </AuthenticatedLayout>
    )
}
