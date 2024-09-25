import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { Button, TextField, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio } from '@mui/material';
import LeafletMap from '@/Components/LeafletMap';
import { useState } from 'react';

export default function NewPlace({auth}) {

    const [lat, setLat] = useState('');
    const [lng, setLng] = useState('');
    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        address: '',
        known_as: '',
        latitude: '',
        longitude: '',
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                                    onChange={(e) => setData('address', e.target.value)}
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
                                    onChange={(e) => setData('known_as', e.target.value)}
                                    error={Boolean(errors.known_as)}
                                    helperText={errors.known_as}
                                />

                                <FormControl component="fieldset" margin="normal">
                                    <FormLabel component="legend">Estado</FormLabel>
                                    <RadioGroup
                                        name="place_type"
                                        value={data.place_type}
                                        onChange={(e) => setData('place_type', e.target.value)}
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
                                    type="number"
                                    inputProps={{
                                        step: 0.000000000000001,
                                        min: -90,
                                        max: 90,
                                        placeholder: "0.00000"
                                    }}
                                    value={data.latitude}
                                    onChange={(e) => setData('latitude', e.target.value)}
                                    error={Boolean(errors.latitude)}
                                    helperText={errors.latitude}
                                />

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
                                    onChange={(e) => setData('longitude', e.target.value)}
                                    error={Boolean(errors.longitude)}
                                    helperText={errors.longitude}
                                />

                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-gray-600">Guardado</p>
                                </Transition>
                            </form>

                            <br />
                            <LeafletMap routing={false} onLocationSelect={updateCoordinates} initialPosition={{lat: data.latitude, lng: data.longitude}} edditing={true}/>

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>

    )
}
