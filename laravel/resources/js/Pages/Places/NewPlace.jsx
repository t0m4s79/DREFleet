import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button, TextField, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, Grid } from '@mui/material';
import LeafletMap from '@/Components/LeafletMap';
import { useState } from 'react';

export default function NewPlace({auth}) {

    const [lat, setLat] = useState(null);
    const [lng, setLng] = useState(null);
    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        address: '',
        known_as: '',
        latitude: '',
        longitude: '',
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('places.create'));
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

    console.log('lat', lat)
    console.log('lng', lng)
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Morada</h2>}
        >
            
            {<Head title='Criar Morada' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar morada</h2>
                            <form onSubmit={handleSubmit} id="newPlaceForm">
                                <input type="hidden" name="_token" value={csrfToken} />

                                <TextField
                                    fullWidth
                                    margin="normal"
                                    id="address"
                                    name="address"
                                    label="Morada"
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

                                <Grid container spacing={3}>
                                    <Grid item xs={6}>
                                        <TextField
                                            fullWidth
                                            margin="normal"
                                            id="latitude"
                                            name="latitude"
                                            label="Latitude"
                                            type="number"
                                            inputProps={{
                                                step: 0.000000000000001,
                                                min: -90,
                                                max: 90,
                                                placeholder: "0.00000"
                                            }}
                                            value={data.latitude}
                                            onChange={handleChange}
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
                                            type="number"
                                            inputProps={{
                                                step: 0.000000000000001,
                                                min: -180,
                                                max: 180,
                                                placeholder: "0.00000"
                                            }}
                                            value={data.longitude}
                                            onChange={handleChange}
                                            error={Boolean(errors.longitude)}
                                            helperText={errors.longitude}
                                        />
                                    </Grid>
                                </Grid>

                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>
                            </form>

                            <br />
                            <LeafletMap routing={false} onLocationSelect={updateCoordinates} initialPosition={{lat: lat, lng: lng}} edditing={true}/>

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>

    )
}
