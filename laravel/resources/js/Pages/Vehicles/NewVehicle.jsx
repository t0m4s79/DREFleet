import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Button, TextField, RadioGroup, FormControl, FormControlLabel, Radio, FormLabel, Grid } from '@mui/material';
import { useState } from 'react';

export default function NewVehicle( {auth} ) {

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        make: '',
        model: '',
        license_plate: '',
        year: '',
        heavy_vehicle: '',
        heavy_type: '',
        heavy_type:'',
        wheelchair_adapted: '',
        wheelchair_certified: '',
        capacity: '',
        fuel_consumption: '',
        status: '',
        current_month_fuel_requests: '',
        fuel_type: '',
        current_kilometrage: ''
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicles.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Veículo</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar veículo</h2>
                            <form onSubmit={handleSubmit} id="newVehicleForm">
                                <input type="hidden" name="_token" value={csrfToken} />

                                <TextField
                                    fullWidth
                                    label="Marca"
                                    id="make"
                                    name="make"
                                    value={data.make}
                                    onChange={(e) => setData('make', e.target.value)}
                                    error={Boolean(errors.make)}
                                    helperText={errors.make && <InputError message={errors.make} />}
                                    margin="normal"
                                />

                                <TextField
                                    fullWidth
                                    label="Modelo"
                                    id="model"
                                    name="model"
                                    value={data.model}
                                    onChange={(e) => setData('model', e.target.value)}
                                    error={Boolean(errors.model)}
                                    helperText={errors.model && <InputError message={errors.model} />}
                                    margin="normal"
                                />

                                <TextField
                                    fullWidth
                                    label="Matrícula (sem '-')"
                                    id="license_plate"
                                    name="license_plate"
                                    placeholder='AAXXBB'
                                    value={data.license_plate}
                                    onChange={(e) => setData('license_plate', e.target.value)}
                                    //inputProps={{ pattern: "[A-Za-z0-9]+", maxLength: 6, title: "Só são permitidos números e letras" }}
                                    error={Boolean(errors.license_plate)}
                                    helperText={errors.license_plate && <InputError message={errors.license_plate} />}
                                    margin="normal"
                                />

                                <TextField
                                    fullWidth
                                    label="Ano"
                                    id="year"
                                    name="year"
                                    placeholder='2024'
                                    value={data.year}
                                    onChange={(e) => setData('year', e.target.value)}
                                    //inputProps={{ pattern: "[0-9]+", maxLength: 4 }}
                                    error={Boolean(errors.year)}
                                    helperText={errors.year && <InputError message={errors.year} />}
                                    margin="normal"
                                />

                                <Grid container>
                                    <Grid item xs={12} md={6}>
                                        <FormControl component="fieldset" margin="normal">
                                            <FormLabel component="legend">Veículo Pesado?</FormLabel>
                                            <RadioGroup
                                                aria-label="heavy_vehicle"
                                                name="heavy_vehicle"
                                                value={data.heavy_vehicle}
                                                onChange={(e) => setData('heavy_vehicle', e.target.value)}
                                            >
                                                <FormControlLabel value="0" control={<Radio />} label="Não" />
                                                <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                            </RadioGroup>
                                            {errors.heavy_vehicle && <InputError message={errors.heavy_vehicle} />}
                                        </FormControl>
                                    </Grid>
                                    <Grid item xs={12} md={6}>
                                        <FormControl component="fieldset" margin="normal">
                                            <FormLabel component="legend">Tipo de Pesado</FormLabel>
                                            <RadioGroup
                                                aria-label="heavy_type"
                                                name="heavy_type"
                                                value={data.heavy_type}
                                                onChange={(e) => setData('heavy_type', e.target.value)}
                                            >
                                                <FormControlLabel value="Mercadorias" control={<Radio />} label="Mercadorias" />
                                                <FormControlLabel value="Passageiros" control={<Radio />} label="Passageiros" />
                                            </RadioGroup>
                                            {errors.heavy_type && <InputError message={errors.heavy_type} />}
                                        </FormControl>
                                    </Grid>
                                    <Grid item xs={12} md={6}>
                                        <FormControl component="fieldset" margin="normal">
                                            <FormLabel component="legend">Adaptado a cadeira de rodas?</FormLabel>
                                            <RadioGroup
                                                aria-label="wheelchair_adapted"
                                                name="wheelchair_adapted"
                                                value={data.wheelchair_adapted}
                                                onChange={(e) => setData('wheelchair_adapted', e.target.value)}
                                            >
                                                <FormControlLabel value="0" control={<Radio />} label="Não" />
                                                <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                            </RadioGroup>
                                            {errors.wheelchair_adapted && <InputError message={errors.wheelchair_adapted} />}
                                        </FormControl>
                                    </Grid>
                                    <Grid item xs={12} md={6}>
                                        <FormControl component="fieldset" margin="normal">
                                            <FormLabel component="legend">Certificado a cadeira de rodas?</FormLabel>
                                            <RadioGroup
                                                aria-label="wheelchair_certified"
                                                name="wheelchair_certified"
                                                value={data.wheelchair_certified}
                                                onChange={(e) => setData('wheelchair_certified', e.target.value)}
                                            >
                                                <FormControlLabel value="0" control={<Radio />} label="Não" />
                                                <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                            </RadioGroup>
                                            {errors.wheelchair_certified && <InputError message={errors.wheelchair_certified} />}
                                        </FormControl>
                                    </Grid>
                                </Grid>

                                <TextField
                                    fullWidth
                                    label="Capacidade (pessoas)"
                                    id="capacity"
                                    name="capacity"
                                    type="number"
                                    inputProps={{ min: 1, max: 100 }}
                                    value={data.capacity}
                                    onChange={(e) => setData('capacity', e.target.value)}
                                    error={Boolean(errors.capacity)}
                                    helperText={errors.capacity && <InputError message={errors.capacity} />}
                                    margin="normal"
                                />

                                <TextField
                                    fullWidth
                                    label="Consumo de combustível (L/100Km)"
                                    id="fuel_consumption"
                                    name="fuel_consumption"
                                    type="number"
                                    step=".001"
                                    placeholder="0.000"
                                    value={data.fuel_consumption}
                                    onChange={(e) => setData('fuel_consumption', e.target.value)}
                                    error={Boolean(errors.fuel_consumption)}
                                    helperText={errors.fuel_consumption && <InputError message={errors.fuel_consumption} />}
                                    margin="normal"
                                />

                                <FormControl component="fieldset" margin="normal">
                                    <FormLabel component="legend">Estado do Veículo</FormLabel>
                                    <RadioGroup
                                        aria-label="status"
                                        name="status"
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value)}
                                    >
                                        <FormControlLabel value="Disponível" control={<Radio />} label="Disponível" />
                                        <FormControlLabel value="Indisponível" control={<Radio />} label="Indisponível" />
                                        <FormControlLabel value="Em manutenção" control={<Radio />} label="Em manutenção" />
                                        <FormControlLabel value="Escondido" control={<Radio />} label="Escondido" />
                                    </RadioGroup>
                                    {errors.status && <InputError message={errors.status} />}
                                </FormControl>

                                <TextField
                                    fullWidth
                                    label="Pedidos de combustível efetuados este mês"
                                    id="current_month_fuel_requests"
                                    name="current_month_fuel_requests"
                                    type="number"
                                    inputProps={{ min: 0, max: 100 }}
                                    value={data.current_month_fuel_requests}
                                    onChange={(e) => setData('current_month_fuel_requests', e.target.value)}
                                    error={Boolean(errors.current_month_fuel_requests)}
                                    helperText={errors.current_month_fuel_requests && <InputError message={errors.current_month_fuel_requests} />}
                                    margin="normal"
                                />

                                <FormControl component="fieldset" margin="normal">
                                    <FormLabel component="legend">Tipo de Combustível</FormLabel>
                                    <RadioGroup
                                        aria-label="fuel_type"
                                        name="fuel_type"
                                        value={data.fuel_type}
                                        onChange={(e) => setData('fuel_type', e.target.value)}
                                    >
                                        <FormControlLabel value="Gasolina 95" control={<Radio />} label="Gasolina 95" />
                                        <FormControlLabel value="Gasolina 98" control={<Radio />} label="Gasolina 98" />
                                        <FormControlLabel value="Gasóleo" control={<Radio />} label="Gasóleo" />
                                        <FormControlLabel value="Híbrido" control={<Radio />} label="Híbrido" />
                                        <FormControlLabel value="Elétrico" control={<Radio />} label="Elétrico" />
                                    </RadioGroup>
                                    {errors.fuel_type && <InputError message={errors.fuel_type} />}
                                </FormControl>
                                
                                <TextField
                                label="Kilometragem Atual"
                                name="current_kilometrage"
                                type="number"
                                value={data.current_kilometrage}
                                onChange={(e) => setData('current_kilometrage', e.target.value)}
                                fullWidth
                                margin="normal"
                                inputProps={{ min: 0}}
                                error={!!errors.current_kilometrage}
                                helperText={errors.current_kilometrage}
                                />

                                <br />

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