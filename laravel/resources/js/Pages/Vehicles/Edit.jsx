import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Radio, RadioGroup, FormControlLabel, FormControl, FormLabel, Button, Grid } from '@mui/material';
import { useForm } from '@inertiajs/react';

export default function Edit({ auth, vehicle}) {

    const { data, setData, put, processing, errors } = useForm({
        make: vehicle.make,
        model: vehicle.model,
        license_plate: vehicle.license_plate,
        heavy_vehicle: vehicle.heavy_vehicle,
        wheelchair_adapted: vehicle.wheelchair_adapted,
        capacity: vehicle.capacity,
        fuel_consumption: vehicle.fuel_consumption,
        status: vehicle.status,
        current_month_fuel_requests: vehicle.current_month_fuel_requests,
    });

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('vehicles.edit', vehicle.id)); // assuming you have a named route 'vehicles.update'
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículo #{vehicle.id}</h2>}
        >

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />

                            <TextField
                                label="Marca"
                                name="make"
                                value={data.make}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                error={!!errors.make}
                                helperText={errors.make}
                            />

                            <TextField
                                label="Modelo"
                                name="model"
                                value={data.model}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                error={!!errors.model}
                                helperText={errors.model}
                            />

                            <TextField
                                label="Matrícula (sem '-')"
                                name="license_plate"
                                value={data.license_plate}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                inputProps={{ pattern: "[A-Za-z0-9]+" }}
                                error={!!errors.license_plate}
                                helperText={errors.license_plate || "Só são permitidos números e letras"}
                            />

                            <Grid container>
                                <Grid item xs={12} md={6}>
                                    <FormControl component="fieldset" margin="normal">
                                        <FormLabel component="legend">Veículo Pesado?</FormLabel>
                                        <RadioGroup
                                            name="heavy_vehicle"
                                            value={data.heavy_vehicle == "Sim" ? 1 : 0}
                                            onChange={handleChange}
                                        >
                                            <FormControlLabel value="0" control={<Radio />} label="Não" />
                                            <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                        </RadioGroup>
                                    </FormControl>
                                </Grid>

                                <Grid item  xs={12} md={6}>
                                    <FormControl component="fieldset" margin="normal">
                                        <FormLabel component="legend">Adaptado a cadeira de rodas?</FormLabel>
                                        <RadioGroup
                                            name="wheelchair_adapted"
                                            value={data.wheelchair_adapted == "Sim" ? 1 : 0}
                                            onChange={handleChange}
                                        >
                                            <FormControlLabel value="0" control={<Radio />} label="Não" />
                                            <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                        </RadioGroup>
                                    </FormControl>
                                </Grid>
                            </Grid>

                            <TextField
                                label="Capacidade (pessoas)"
                                name="capacity"
                                type="number"
                                value={data.capacity}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                inputProps={{ min: 1, max: 100 }}
                                error={!!errors.capacity}
                                helperText={errors.capacity}
                            />

                            <TextField
                                label="Consumo de combustível (L/100Km)"
                                name="fuel_consumption"
                                type="number"
                                value={data.fuel_consumption}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                inputProps={{ step: "0.001" }}
                                error={!!errors.fuel_consumption}
                                helperText={errors.fuel_consumption}
                            />

                            {/* Use MUI Radio for status */}
                            <FormControl component="fieldset" margin="normal">
                                <FormLabel component="legend">Estado</FormLabel>
                                <RadioGroup
                                    name="status"
                                    value={data.status}
                                    onChange={handleChange}
                                >
                                    <FormControlLabel value="Disponível" control={<Radio />} label="Disponível" />
                                    <FormControlLabel value="Indisponível" control={<Radio />} label="Indisponível" />
                                    <FormControlLabel value="Em manutenção" control={<Radio />} label="Em manutenção" />
                                    <FormControlLabel value="Escondido" control={<Radio />} label="Escondido" />
                                </RadioGroup>
                            </FormControl>

                            <TextField
                                label="Pedidos de combustível efetuados este mês"
                                name="current_month_fuel_requests"
                                type="number"
                                value={data.current_month_fuel_requests}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                inputProps={{ min: 0, max: 100 }}
                                error={!!errors.current_month_fuel_requests}
                                helperText={errors.current_month_fuel_requests}
                            />

                            <Button type="submit" variant='outlined'>Submeter</Button>

                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
