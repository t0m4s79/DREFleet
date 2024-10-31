import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputLabel from '@/Components/InputLabel';
import { TextField, Radio, RadioGroup, FormControlLabel, FormControl, FormLabel, Button, Autocomplete } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';

{/*TODO: Condition should turn grey and auto select expired if present date is bigger than expiration_date */}
export default function EditVehicleAccessory({ auth, vehicleAccessory, vehicles}) {

    const [isEditMode, setisEditMode] = useState(false)

    const initialData = {
        name: vehicleAccessory.name,
        condition: vehicleAccessory.condition,
        expiration_date: vehicleAccessory.expiration_date,
        vehicle_id: vehicleAccessory.vehicle_id,
    }

    const { data, setData, put, processing, errors } = useForm({...initialData});

    const toggleEdit = () => {
        if (isEditMode) {
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('vehicleAccessories.edit', vehicleAccessory.id));
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })


    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Acessório #{vehicleAccessory.id}</h2>}
        >
            
            {<Head title='Editar Acessório' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit} noValidate >
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
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                fullWidth
                                margin="normal"
                                error={!!errors.name}
                                helperText={errors.name}
                            />

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                <FormLabel component="legend">Condição</FormLabel>
                                <RadioGroup
                                    aria-label="condition"
                                    name="condition"
                                    value={data.condition}
                                    onChange={(e) => setData('condition', e.target.value)}
                                >
                                    <FormControlLabel value="Expirado" control={<Radio />} label="Expirado" />
                                    <FormControlLabel value="Danificado" control={<Radio />} label="Danificado" />
                                    <FormControlLabel value="Aceitável" control={<Radio />} label="Aceitável" />
                                </RadioGroup>
                                {errors.condition && <InputError message={errors.condition} />}
                            </FormControl>

                            <InputLabel htmlFor="expiration_date" value="Data de Validade (opcional)" />
                            <TextField
                                id='expiration_date'
                                name='expiration_date'
                                type="date"
                                fullWidth
                                value={data.expiration_date}
                                onChange={(e) => setData('expiration_date', e.target.value)}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                error={errors.expiration_date}
                                helperText={errors.expiration_date}
                                sx={{ mb: 2 }}
                            />

                            <Autocomplete
                                id="vehicle"
                                options={vehicleList}
                                getOptionLabel={(option) => option.label}
                                value={vehicleList.find(vehicle => vehicle.value === data.vehicle_id) || null}
                                onChange={(e,value) => setData('vehicle_id', value.value)}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
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
                            <br />
                      </form>
                      
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
