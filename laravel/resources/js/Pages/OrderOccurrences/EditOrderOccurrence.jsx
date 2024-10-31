import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, Button, TextField, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, } from '@mui/material';
import React, { useState } from 'react';

export default function EditOrderOccurrence({ auth, occurrence, orders }) {
    const [isEditMode, setisEditMode] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        type: occurrence.type,
        vehicle_towed: occurrence.vehicle_towed,
        description: occurrence.description,
        order_id: occurrence.order_id,
    });

    const toggleEdit = () => {
        setisEditMode(!isEditMode);
    };

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('orderOccurrences.edit', occurrence.id));
    };

    const orderList = orders.map((order) => {
        return {
            value: order.id, 
            label: 
                `Pedido id ${order.id}: - ${order.expected_begin_date} a ${order.expected_end_date} ` +
                `|| Veículo id ${order.vehicle.id}: ${order.vehicle.make} ${order.vehicle.model} ${order.vehicle.license_plate} ` +
                `|| Condutor id ${order.driver.user_id}: ${order.driver.name} ${order.driver.license_number}`
        }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Ocorrência #{occurrence.id}</h2>}
        >

            {<Head title='Editar Ocorrência' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit} noValidate>
                            <input type="hidden" name="_token" value={csrfToken} />

                            <div className='mb-4'>
                                {!isEditMode ? 
                                    <Button
                                        variant="contained"
                                        color="primary"
                                        disabled={processing}
                                        onClick={toggleEdit}
                                    >
                                        Editar
                                    </Button> 
                                    :
                                    <>
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
                                    </>
                                }
                                <br /> <br />
                            </div>

                            {/* Autocomplete for Order List */}
                            <Autocomplete
                                id="order_id"
                                options={orderList}
                                getOptionLabel={(option) => option.label}
                                onChange={(event, newValue) => setData('order_id', newValue ? newValue.value : '')}
                                value={orderList.find(order => order.value === data.order_id) || null}
                                renderInput={(params) => (
                                    <TextField
                                        {...params}
                                        label="Pedido"
                                        fullWidth
                                        disabled={!isEditMode}
                                        error={Boolean(errors.order_id)}
                                        helperText={errors.order_id}
                                    />
                                )}
                                sx={{ mb: 2 }}
                            />

                            {/* Radio Buttons for Occurrence Type */}
                            <FormControl component="fieldset" margin="normal">
                                <FormLabel component="legend">Tipo de Ocorrência</FormLabel>
                                <RadioGroup
                                    name="type"
                                    value={data.type}
                                    onChange={handleChange}
                                >
                                    <div style={{ display: 'flex', flexDirection: 'column', marginRight: '20px' }}>
                                        <FormControlLabel value="Reparações" control={<Radio />} label="Reparações" disabled={!isEditMode} />
                                        <FormControlLabel value="Lavagens" control={<Radio />} label="Lavagens" disabled={!isEditMode} />
                                    </div>
                                    <div style={{ display: 'flex', flexDirection: 'column' }}>
                                        <FormControlLabel value="Manutenções" control={<Radio />} label="Manutenções" disabled={!isEditMode} />
                                        <FormControlLabel value="Outros" control={<Radio />} label="Outros" disabled={!isEditMode} />
                                    </div>
                                </RadioGroup>
                            </FormControl>

                            <FormControl component="fieldset" margin="normal">
                                    <FormLabel component="legend">Veículo Rebocado</FormLabel>
                                    <RadioGroup
                                        name="vehicle_towed"
                                        value={data.vehicle_towed}
                                        onChange={handleChange}
                                    >
                                        <div style={{ display: 'flex', flexDirection: 'column'}}>
                                            <FormControlLabel value="1" control={<Radio />} label="Sim" disabled={!isEditMode}/>
                                            <FormControlLabel value="0" control={<Radio />} label="Não" disabled={!isEditMode}/>
                                        </div>
                                    </RadioGroup>
                                </FormControl>

                            {/* Text Field for Description */}
                            <TextField
                                label="Descrição da Ocorrência"
                                multiline
                                rows={4}
                                fullWidth
                                value={data.description}
                                onChange={(e) => {
                                    const newValue = e.target.value;
                                    if (newValue.length <= 500) {
                                        setData('description', newValue);
                                    }
                                }}
                                error={Boolean(errors.description)}
                                helperText={errors.description}
                                disabled={!isEditMode}
                                sx={{ mb: 2 }}
                            />

                            {/* Character Counter */}
                            {isEditMode && (
                                <div style={{ textAlign: 'right', color: data.description.length >= 500 ? 'red' : 'black' }}>
                                    {500 - data.description.length} caracteres restantes
                                </div>
                            )}

                        </form>

                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}