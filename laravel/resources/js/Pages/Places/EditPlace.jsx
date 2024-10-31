import React from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Button, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, Grid } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import { useState } from 'react';

export default function EditPlace({auth, place }) {

    const [lat, setLat] = useState(place.coordinates.coordinates[1]);
    const [lng, setLng] = useState(place.coordinates.coordinates[0]);  
    const [isEditMode, setisEditMode] = useState(false)

    const initialData = {
        address: place.address,
        known_as: place.known_as,
        place_type: place.place_type,
        latitude: place.coordinates.coordinates[1],
        longitude: place.coordinates.coordinates[0],
    }

    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, put, errors, processing } = useForm({...initialData});

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const toggleEdit = () => {
        if (isEditMode) {
            updateCoordinates(place.coordinates.coordinates[1], place.coordinates.coordinates[0])
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);

        // Update map coordinates if latitude or longitude fields are changed
        if (name === 'latitude') {
            updateCoordinates(value, data.longitude);
        } else if (name === 'longitude') {
            updateCoordinates(data.latitude, value);
        }
    };

    const updateCoordinates = (latitude, longitude) => {
        setLat(latitude);
        setLng(longitude);
        setData({
            ...data, // Keep other fields intact
            latitude: latitude,
            longitude: longitude,
          });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('places.edit', place.id));
    };

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Morada #{place.id}</h2>}
        >

            {<Head title='Editar Morada' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken}/>

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
                                fullWidth
                                margin="normal"
                                id="address"
                                name="address"
                                label="Nome"
                                value={data.address}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                error={Boolean(errors.address)}
                                helperText={errors.address}
                            />

                            <TextField
                                fullWidth
                                margin="normal"
                                id="known_as"
                                name="known_as"
                                label="Conhecido como"
                                value={data.known_as}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                error={Boolean(errors.known_as)}
                                helperText={errors.known_as}
                            />

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                <FormLabel component="legend">Estado</FormLabel>
                                <RadioGroup
                                    name="place_type"
                                    value={data.place_type}
                                    onChange={handleChange}
                                >
                                    <FormControlLabel value="Residência" control={<Radio />} label="Residência" />
                                    <FormControlLabel value="Escola" control={<Radio />} label="Escola" />
                                    <FormControlLabel value="Outros" control={<Radio />} label="Outros" />
                                </RadioGroup>
                            </FormControl>

                            <Grid container spacing={3}>
                                <Grid item xs={6}>
                                    <TextField
                                        fullWidth
                                        margin="normal"
                                        id="latitude"
                                        name="latitude"
                                        label="Latitude"
                                        value={data.latitude}
                                        onChange={handleChange}
                                        className={!isEditMode ? 'read-only-field' : ''}
                                        disabled={!isEditMode}
                                        error={Boolean(errors.latitude)}
                                        helperText={errors.latitude}
                                    />
                                </Grid>

                                <Grid item xs={6}>
                                    <TextField
                                        fullWidth
                                        margin="normal"
                                        id="longitude"
                                        name="longitude"
                                        label="Longitude"
                                        value={data.longitude}
                                        onChange={handleChange}
                                        className={!isEditMode ? 'read-only-field' : ''}
                                        disabled={!isEditMode}
                                        error={Boolean(errors.longitude)}
                                        helperText={errors.longitude}
                                    />
                                </Grid>
                            </Grid>
                        </form>
                       
                        <br />
                            <LeafletMap routing={false} onLocationSelect={updateCoordinates} initialPosition={{lat: lat, lng: lng}} edditing={true}/>

                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}