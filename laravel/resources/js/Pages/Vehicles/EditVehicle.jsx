import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Radio, RadioGroup, FormControlLabel, FormControl, FormLabel, Button, Grid } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';

{/*TODO: VEHICLE IMAGE SELECT */}
export default function EditVehicle({ auth, vehicle}) {

    const [isEditMode, setisEditMode] = useState(false)

    const { data, setData, put, processing, errors } = useForm({
        make: vehicle.make,
        model: vehicle.model,
        license_plate: vehicle.license_plate,
        year: vehicle.year,
        heavy_vehicle: vehicle.heavy_vehicle,
        heavy_type: vehicle.heavy_type,
        wheelchair_adapted: vehicle.wheelchair_adapted,
        wheelchair_certified: vehicle.wheelchair_certified,
        capacity: vehicle.capacity,
        fuel_consumption: vehicle.fuel_consumption,
        status: vehicle.status,
        current_month_fuel_requests: vehicle.current_month_fuel_requests,
        fuel_type: vehicle.fuel_type,
        current_kilometrage: vehicle.current_kilometrage,
    });

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleHeavyChange = () => {
        if(data.heavy_vehicle != 1)
            setData('heavy_type', null)
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        handleHeavyChange();
        put(route('vehicles.edit', vehicle.id)); // assuming you have a named route 'vehicles.update'
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículo #{vehicle.id}</h2>}
        >

            {<Head title='Editar Veículo' />}

            <div className="py-12">
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
                                    variant="contained"
                                    color="error"
                                    disabled={processing}
                                    onClick={toggleEdit}
                                >
                                    Cancelar Edição
                                </Button>
                                <Button
                                    type="submit"
                                    variant="contained"
                                    color="primary"
                                    disabled={processing}
                                >
                                    Submeter
                                </Button>
                            </div>)}

                            <TextField
                                label="Marca"
                                name="make"
                                value={data.make}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
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
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
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
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                inputProps={{ pattern: "[A-Za-z0-9]+" }}
                                error={!!errors.license_plate}
                                helperText={errors.license_plate || "Só são permitidos números e letras"}
                            />

                            <TextField
                                label="Ano"
                                name="year"
                                value={data.year}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                inputProps={{ pattern: "[0-9]+" }}
                                error={!!errors.year}
                                helperText={errors.year || "Só são permitidos números"}
                            />

                            <Grid container>
                                <Grid item xs={12} md={6}>
                                    <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                        <FormLabel component="legend">Veículo Pesado?</FormLabel>
                                        <RadioGroup
                                            name="heavy_vehicle"
                                            value={data.heavy_vehicle}
                                            onChange={handleChange}
                                        >
                                            <FormControlLabel value="0" control={<Radio />} label="Não" />
                                            <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                        </RadioGroup>
                                    </FormControl>
                                </Grid>

                                <FormControl component="fieldset" margin="normal" disabled={!isEditMode || data.heavy_vehicle == '0'}>
                                    <FormLabel component="legend">Tipo de Pesado</FormLabel>
                                    <RadioGroup
                                        name="heavy_type"
                                        value={data.heavy_type}
                                        onChange={handleChange}
                                    >
                                        <FormControlLabel value="Mercadorias" control={<Radio />} label="Mercadorias" />
                                        <FormControlLabel value="Passageiros" control={<Radio />} label="Passageiros" />
                                    </RadioGroup>
                                </FormControl>

                                <Grid item  xs={12} md={6}>
                                    <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                        <FormLabel component="legend">Adaptado a cadeira de rodas?</FormLabel>
                                        <RadioGroup
                                            name="wheelchair_adapted"
                                            value={data.wheelchair_adapted}
                                            onChange={handleChange}
                                        >
                                            <FormControlLabel value="0" control={<Radio />} label="Não" />
                                            <FormControlLabel value="1" control={<Radio />} label="Sim" />
                                        </RadioGroup>
                                    </FormControl>
                                </Grid>

                                <Grid item  xs={12} md={6}>
                                    <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                        <FormLabel component="legend">Certificado a cadeira de rodas?</FormLabel>
                                        <RadioGroup
                                            name="wheelchair_certified"
                                            value={data.wheelchair_certified}
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
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
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
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                inputProps={{ step: "0.001" }}
                                error={!!errors.fuel_consumption}
                                helperText={errors.fuel_consumption}
                            />

                            {/* Use MUI Radio for status */}
                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
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
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                inputProps={{ min: 0, max: 100 }}
                                error={!!errors.current_month_fuel_requests}
                                helperText={errors.current_month_fuel_requests}
                            />

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
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
                                {errors.status && <InputError message={errors.status} />}
                            </FormControl>

                            <TextField
                                label="Kilometragem Atual"
                                name="current_kilometrage"
                                type="number"
                                value={data.current_kilometrage}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                inputProps={{ min: 0}}
                                error={!!errors.current_kilometrage}
                                helperText={errors.current_kilometrage}
                            />

                            <br />
                        </form>
                        
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
