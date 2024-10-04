import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { Button, TextField, Grid, Autocomplete } from '@mui/material';
import { useState } from 'react';

export default function NewVehicleAccessory( {auth, vehicles} ) {

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        name: '',
        issue_date: '',
        expiration_date: '',
        vehicle_id: '',
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicleDocuments.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Documento</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar documento de veículo</h2>
                            <form onSubmit={handleSubmit} id="newVehicleForm" noValidate>
                                <input type="hidden" name="_token" value={csrfToken} />

                                <TextField
                                    fullWidth
                                    label="Nome"
                                    id="name"
                                    name="name"
                                    value={data.make}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={Boolean(errors.name)}
                                    helperText={errors.name && <InputError message={errors.name} />}
                                    margin="normal"
                                />

                                <Grid container spacing={3}>
                                    <Grid item xs={6}>
                                        <InputLabel htmlFor="issue_date" value="Data de Emissão" />
                                        <TextField
                                            //label="Data e Hora de Início"
                                            id='issue_date'
                                            name='issue_date'
                                            type="datetime-local"
                                            fullWidth
                                            value={data.issue_date}
                                            onChange={(e) => setData('issue_date', e.target.value)}
                                            error={errors.issue_date}
                                            helperText={errors.issue_date}
                                            sx={{ mb: 2 }}
                                        />
                                    </Grid>

                                    <Grid item xs={6}>
                                        <InputLabel htmlFor="expiration_date" value="Data de Validade" />
                                        <TextField
                                            //label="Data e Hora de Início"
                                            id='expiration_date'
                                            name='expiration_date'
                                            type="date"
                                            fullWidth
                                            value={data.expiration_date}
                                            onChange={(e) => setData('expiration_date', e.target.value)}
                                            error={errors.expiration_date}
                                            helperText={errors.expiration_date}
                                            sx={{ mb: 2 }}
                                        />
                                    </Grid>
                                </Grid>

                                <br />

                                <Autocomplete
                                    id="vehicle"
                                    options={vehicleList}
                                    getOptionLabel={(option) => option.label}
                                    onChange={(e,value) => setData('vehicle_id', value.value)}
                                    renderInput={(params) => (
                                        <TextField
                                            {...params}
                                            label="Veículo"
                                            fullWidth
                                            value={data.vehicle_id}
                                            error={errors.vehicle_id}
                                            helperText={errors.vehicle_id}
                                        />
                                    )}
                                    sx={{ mb: 2 }}
                                />

                                <Button variant="outlined" type="submit" disabled={processing}>
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
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )

}