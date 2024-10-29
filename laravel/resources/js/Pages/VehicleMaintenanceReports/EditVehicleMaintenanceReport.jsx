import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { Autocomplete, Button, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, TextField, Grid } from '@mui/material';

{/*TODO: ITEM INDIVIDUAL COST -> ITEM_1: COST, ITEM_2:COST,... SIMILAR TO VEHICLE DOCUMENTS DATA FIELD */}
{/*TODO: SHOW STATUS ACCORDING TO DATE VALUES */}
export default function EditVehicleKilometrageReports( {auth, report, vehicles} ) {
    console.log(report);
    const [isEditMode, setisEditMode] = useState(false)

    const { data, setData, put, processing, errors } = useForm({
        begin_date: report.begin_date,
        end_date: report.end_date,
        type: report.type,
        description: report.description,
        kilometrage: report.kilometrage,
        total_cost: report.total_cost,
        items_cost: report.items_cost,
        service_provider: report.service_provider,
        vehicle_id: report.vehicle_id,
    });

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('vehicleMaintenanceReports.edit', report.id)); // assuming you have a named route 'vehicles.update'
    };

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Registo de Manutenção de Veículo</h2>}
        >

            {<Head title='Registo de Manutenção de Veículo' />}

            <div className='py-12'>
                    <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className='p-6'>
                                <div className='my-6'>                                    
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
                                            </div>)
                                        }

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

                                        <Grid container spacing={3}>
                                            <Grid item xs={6}>
                                                <InputLabel htmlFor="begin_date" value="Data de Início" />
                                                <TextField
                                                    id='begin_date'
                                                    name='begin_date'
                                                    type="date"
                                                    fullWidth
                                                    value={data.begin_date}
                                                    onChange={(e) => setData('begin_date', e.target.value)}
                                                    className={!isEditMode ? 'read-only-field' : ''}
                                                    disabled={!isEditMode}
                                                    error={errors.begin_date}
                                                    helperText={errors.begin_date}
                                                    sx={{ mb: 2 }}
                                                />
                                            </Grid>

                                            <Grid item xs={6}>
                                                <InputLabel htmlFor="end_date" value="Data de Fim (opcional)" />
                                                <TextField
                                                    id='end_date'
                                                    name='end_date'
                                                    type="date"
                                                    fullWidth
                                                    value={data.end_date}
                                                    onChange={(e) => setData('end_date', e.target.value)}
                                                    className={!isEditMode ? 'read-only-field' : ''}
                                                    disabled={!isEditMode}
                                                    error={errors.end_date}
                                                    helperText={errors.end_date}
                                                    sx={{ mb: 2 }}
                                                />
                                            </Grid>
                                        </Grid>

                                        <Grid container spacing={3}>
                                            <Grid item xs={6}>
                                                <TextField
                                                    label="Kilometragem (opcional)"
                                                    name="kilometrage"
                                                    type="number"
                                                    value={data.kilometrage}
                                                    onChange={(e) => setData('kilometrage', e.target.value)}
                                                    fullWidth
                                                    margin="normal"
                                                    inputProps={{ min: 0}}
                                                    className={!isEditMode ? 'read-only-field' : ''}
                                                    disabled={!isEditMode}
                                                    error={!!errors.kilometrage}
                                                    helperText={errors.kilometrage}
                                                />      
                                            </Grid>

                                            <Grid item xs={6}>
                                                <TextField
                                                    label="Providenciador de Serviços (opcional)"
                                                    multiline
                                                    rows={2}
                                                    fullWidth
                                                    value={data.service_provider}
                                                    onChange={(e) => {
                                                        const newValue = e.target.value;
                                                        // Check if the new value length is less than or equal to 500
                                                        if (newValue.length <= 100) {
                                                            setData('service_provider', newValue); // Update state
                                                        }
                                                    }}
                                                    className={!isEditMode ? 'read-only-field' : ''}
                                                    disabled={!isEditMode}
                                                    error={errors.service_provider}
                                                    helperText={errors.service_provider}
                                                    sx={{ mb: 2 }}
                                                />
                                                <div style={{ textAlign: 'right', color: data.service_provider.length >= 100 ? 'red' : 'black' }}>
                                                    {100 - data.service_provider.length} caracteres restantes
                                                </div>
                                            </Grid>
                                        </Grid>

                                        <FormControl component="fieldset" margin="normal" className={!isEditMode ? 'read-only-field' : ''} disabled={!isEditMode}>
                                            <FormLabel component="legend">Tipo</FormLabel>
                                            <RadioGroup
                                                aria-label="type"
                                                name="type"
                                                value={data.type}
                                                onChange={(e) => setData('type', e.target.value)}
                                            >
                                                <Grid container spacing={2}>
                                                    <Grid item xs={6}>
                                                        <FormControlLabel value="Manutenção" control={<Radio />} label="Manutenção" />
                                                    </Grid>
                                                    <Grid item xs={6}>
                                                        <FormControlLabel value="Anomalia" control={<Radio />} label="Anomalia" />
                                                    </Grid>
                                                    <Grid item xs={6}>
                                                        <FormControlLabel value="Reparação" control={<Radio />} label="Reparação" />
                                                    </Grid>
                                                    <Grid item xs={6}>
                                                        <FormControlLabel value="Outros" control={<Radio />} label="Outros" />
                                                    </Grid>
                                                </Grid>
                                            </RadioGroup>
                                            {errors.type && <InputError message={errors.type} />}
                                        </FormControl>

                                        <br /> <br />

                                        <TextField
                                            label="Descrição"
                                            multiline
                                            rows={4}
                                            fullWidth
                                            value={data.description}
                                            onChange={(e) => {
                                                const newValue = e.target.value;
                                                // Check if the new value length is less than or equal to 500
                                                if (newValue.length <= 500) {
                                                    setData('description', newValue); // Update state
                                                }
                                            }}
                                            className={!isEditMode ? 'read-only-field' : ''}
                                            disabled={!isEditMode}
                                            error={errors.description}
                                            helperText={errors.description}
                                            sx={{ mb: 2 }}
                                        />

                                        <div style={{ textAlign: 'right', color: data.description.length >= 500 ? 'red' : 'black' }}>
                                            {500 - data.description.length} caracteres restantes
                                        </div>

                                        <TextField
                                            fullWidth
                                            label="Custo Total (opcional)"
                                            id="total_cost"
                                            name="total_cost"
                                            type="number"
                                            step=".01"
                                            placeholder="0.00"
                                            value={data.total_cost}
                                            onChange={(e) => setData('total_cost', e.target.value)}
                                            className={!isEditMode ? 'read-only-field' : ''}
                                            disabled={!isEditMode}
                                            error={Boolean(errors.total_cost)}
                                            helperText={errors.total_cost && <InputError message={errors.total_cost} />}
                                            margin="normal"
                                        />
                                    </form> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </AuthenticatedLayout>
    )
}
