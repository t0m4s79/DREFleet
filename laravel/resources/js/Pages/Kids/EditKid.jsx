import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button, Checkbox, FormControl, FormControlLabel, FormLabel, InputLabel, ListItemText, MenuItem, OutlinedInput, Radio, RadioGroup, Select, TextField } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';

export default function EditKid({auth, kid, availablePlaces}) {

    const [selectedAddPlaces, setSelectedAddPlaces] = useState([]);                 // state variable that holds the places to be added
    const [selectedRemovePlaces, setSelectedRemovePlaces] = useState([]);           // state variable that holds the places to be removed
    //console.log(kid)
    const [isEditMode, setisEditMode] = useState(false)
    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, put, errors, processing } = useForm({
        name: kid.name,
        wheelchair: kid.wheelchair,
        addPlaces: [],
        removePlaces: [],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
    };

    // Handle changes when a place is added
    const handleAddPlacesChange = (event) => {
        const newSelectedAddPlaces = event.target.value;
        setSelectedAddPlaces(newSelectedAddPlaces);
        setData('addPlaces', newSelectedAddPlaces);
    };
    
    // Handle changes when a place is removed
    const handleRemovePlacesChange = (event) => {
        const newSelectedRemovePlaces = event.target.value;
        setSelectedRemovePlaces(newSelectedRemovePlaces);
        setData('removePlaces', newSelectedRemovePlaces);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('kids.edit', kid.id));
    };

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Criança #{kid.id}</h2>}
        >

            {<Head title='Editar Criança' />}

            <div className="py-12 text-gray-800">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />

                            { isEditMode === false ? 
                                (<div className='mb-4'>
                                    <Button
                                    variant="contained"
                                    color="primary"
                                    disabled={processing}
                                    onClick={toggleEdit}
                                >
                                    Editar
                                    </Button>
                                </div>) : 

                            (<div className='mb-4 space-x-4'>
                                <Button 
                                    variant="outlined"
                                    color="error"
                                    disabled={processing}
                                    onClick={toggleEdit}
                                >
                                    Cancelar Edição
                                </Button>
                                <Button
                                    type="submit"
                                    variant="outlined"
                                    color="primary"
                                    disabled={processing}
                                >
                                    Submeter
                                </Button>
                            </div>)}
                            
                            <TextField
                                label="Nome"
                                variant="outlined"
                                fullWidth
                                margin="normal"
                                id="name"
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                error={!!errors.name}
                                helperText={errors.name && <InputError message={errors.name} />}
                            />

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                <FormLabel component="legend">Utiliza cadeira de rodas?</FormLabel>
                                <RadioGroup
                                    aria-label="wheelchair"
                                    name="wheelchair"
                                    value={data.wheelchair}
                                    onChange={handleChange}
                                    row
                                >
                                    <FormControlLabel value="0" control={<Radio />} label="Não" />
                                    <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                </RadioGroup>
                                {errors.wheelchair && <InputError message={errors.wheelchair} />}
                            </FormControl>


                            <p>Adicionar Morada(s)</p>
                            <FormControl sx={{ m: 1, minWidth: 300 }} className={!isEditMode ? 'read-only-field' : ''} disabled={!isEditMode}>
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

                            <p>Retirar Morada(s)</p>
                            <FormControl sx={{ m: 1, minWidth: 300 }} className={!isEditMode ? 'read-only-field' : ''} disabled={!isEditMode}>
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
                        </form>

                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}