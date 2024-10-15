import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputLabel from '@/Components/InputLabel';
import { TextField, Grid, Button, Autocomplete } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';

{/*TODO: VEHICLE IMAGE SELECT */}
export default function EditVehicleDocument({ auth, vehicleDocument, vehicles}) {

    const [isEditMode, setisEditMode] = useState(false)

    const { data, setData, put, processing, errors } = useForm({
        name: vehicleDocument.name,
        issue_date: vehicleDocument.issue_date,
        expiration_date: vehicleDocument.expiration_date,
        vehicle_id: vehicleDocument.vehicle_id,
    });

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('vehicleDocuments.edit', vehicleDocument.id)); // assuming you have a named route 'vehicles.update'
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })


    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Documento #{vehicleDocument.id}</h2>}
        >

            {<Head title='Editar Documento' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

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

                        <form onSubmit={handleSubmit} noValidate>
                            <input type="hidden" name="_token" value={csrfToken} />

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

                            <Grid container spacing={3}>
                                <Grid item xs={6}>
                                    <InputLabel htmlFor="issue_date" value="Data de Emissão" />
                                    <TextField
                                        id='issue_date'
                                        name='issue_date'
                                        type="date"
                                        fullWidth
                                        value={data.issue_date}
                                        onChange={(e) => setData('issue_date', e.target.value)}
                                        className={!isEditMode ? 'read-only-field' : ''}
                                        disabled={!isEditMode}
                                        error={errors.issue_date}
                                        helperText={errors.issue_date}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                                <Grid item xs={6}>
                                <InputLabel htmlFor="expiration_date" value="Data de Validade" />
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
                                </Grid>
                            </Grid>


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
