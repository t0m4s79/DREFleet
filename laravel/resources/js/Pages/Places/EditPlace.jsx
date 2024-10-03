import React from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Button, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import { useState } from 'react';

export default function EditPlace({auth, place, kids}) {

    const [lat, setLat] = useState('');
    const [lng, setLng] = useState('');

    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, put, errors, processing } = useForm({    //TODO: TRY TO CHANGE COORDINATES ATTRIBUTE TO SOMETHING MORE SIMPLE
        address: place.address,
        known_as: place.known_as,
        place_type: place.place_type,
        latitude: place.coordinates.coordinates[1],
        longitude: place.coordinates.coordinates[0],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
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

            {/*<Head title={'Moradas'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken}/>

                            <TextField
                                fullWidth
                                margin="normal"
                                id="address"
                                name="address"
                                label="Nome"
                                value={data.address}
                                onChange={handleChange}
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
                                error={Boolean(errors.known_as)}
                                helperText={errors.known_as}
                            />

                            <FormControl component="fieldset" margin="normal">
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


                            <TextField
                                fullWidth
                                margin="normal"
                                id="latitude"
                                name="latitude"
                                label="Latitude"
                                value={data.latitude}
                                onChange={handleChange}
                                error={Boolean(errors.latitude)}
                                helperText={errors.latitude}
                            />

                            <TextField
                                fullWidth
                                margin="normal"
                                id="longitude"
                                name="longitude"
                                label="Longitude"
                                value={data.longitude}
                                onChange={handleChange}
                                error={Boolean(errors.longitude)}
                                helperText={errors.longitude}
                            />

                            <Button
                                type="submit"
                                variant="outlined"
                                color="primary"
                                disabled={processing}
                                sx={{ mt: 3 }}
                            >
                                Submeter
                            </Button>
                        </form>

                        <br />
                            <LeafletMap routing={false} onLocationSelect={updateCoordinates} initialPosition={{lat: data.latitude, lng: data.longitude}} edditing={true}/>

                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}