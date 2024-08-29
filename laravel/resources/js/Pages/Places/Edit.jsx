import React from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Button } from '@mui/material';
import { useForm } from '@inertiajs/react';

export default function Edit({auth, place, kids}) {

    // Initialize state with kid data
    const { data, setData, put, errors, processing } = useForm({    //TODO: TRY TO CHANGE COORDINATES ATTRIBUTE TO SOMETHING MORE SIMPLE
        address: place.address,
        known_as: place.known_as,
        latitude: place.coordinates.coordinates[0],
        longitude: place.coordinates.coordinates[1],
    });
    
    const kid = kids.map((kid)=>(
        <option value={kid.id}>{kid.id} - {kid.name}</option>
    ));

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('places.edit', place.id));
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
                                variant="contained"
                                color="primary"
                                disabled={processing}
                                sx={{ mt: 3 }}
                            >
                                Submeter
                            </Button>
                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}