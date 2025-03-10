import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { Autocomplete, Button, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, TextField, Grid, Typography } from '@mui/material';
import { useState } from 'react';
import { debounce } from 'lodash';
import { Add, Remove } from '@mui/icons-material';

{/*TODO: SHOW STATUS ACCORDING TO DATE VALUES */ }
export default function NewVehicleMaintenanceReports({ auth, vehicles }) {
    const [itemsCost, setItemsCost] = useState([{}]); // Empty object for dynamic keys
    const [titleErrors, setTitleErrors] = useState([]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const { data, setData, post, errors, processing } = useForm({
        begin_date: '',
        end_date: '',
        type: '',
        description: '',
        kilometrage: '',
        total_cost: '',
        items_cost: {},
        service_provider: '',
        vehicle_id: '',
    });

    const debouncedSetItemsCost = debounce((key, value) => {
        setData(key, value);
    }, 300);

    // Handle dynamic input for title and description
    const handleInputChange = (index, event) => {
        const newItemsCost = [...itemsCost];
        const currentKey = Object.keys(newItemsCost[index])[0];
        const currentValue = Object.values(newItemsCost[index])[0];

        const newTitleErrors = [...titleErrors];

        if (event.target.name === 'title') {
            const newTitle = event.target.value;
            const isDuplicate = newItemsCost.some((doc, idx) => Object.keys(doc)[0] === newTitle && idx !== index);

            if (isDuplicate) {
                newTitleErrors[index] = "Este título já existe.";
            } else {
                newTitleErrors[index] = null;
            }

            newItemsCost[index] = { [newTitle]: currentValue || '' };
            setTitleErrors(newTitleErrors);
        } else if (event.target.name === 'description') {
            newItemsCost[index] = { [currentKey || '']: event.target.value };
        }

        setItemsCost(newItemsCost);

        // Only debounce the internal state update, not the submission
        const flattenedData = newItemsCost.reduce((acc, doc) => {
            const key = Object.keys(doc)[0];
            const value = doc[key];

            if (key && value) {
                acc[key] = value;
            }

            return acc;
        }, {});

        // Use debounced setData for typing
        debouncedSetItemsCost('items_cost', flattenedData);
    };

    const addItemsCost = () => {
        setItemsCost([...itemsCost, {}]);  // Add an empty object for a new key-value input
    };

    const removeItemsCost = (index) => {
        const newItemsCost = [...itemsCost];
        newItemsCost.splice(index, 1);
        setItemsCost(newItemsCost);
        setData('items_cost', newItemsCost);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicleMaintenanceReports.create'));
    };

    const vehicleList = vehicles.map((vehicle) => {
        return { value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}` }
    })

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Registo de Manutenção de Veículo</h2>}
        >

            {<Head title='Registo de Manutenção de Veículo' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>
                            <div className='my-6'>
                                <form onSubmit={handleSubmit}>
                                    <input type="hidden" name="_token" value={csrfToken} />

                                    <Autocomplete
                                        id="vehicle"
                                        options={vehicleList}
                                        getOptionLabel={(option) => option.label}
                                        onChange={(e, value) => setData('vehicle_id', value.value)}
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
                                                inputProps={{ min: 0 }}
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
                                                error={errors.service_provider}
                                                helperText={errors.service_provider}
                                                sx={{ mb: 2 }}
                                            />
                                            <div style={{ textAlign: 'right', color: data.service_provider.length >= 100 ? 'red' : 'black' }}>
                                                {100 - data.service_provider.length} caracteres restantes
                                            </div>
                                        </Grid>
                                    </Grid>

                                    <FormControl component="fieldset" margin="normal">
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

                                    <FormControl fullWidth margin='normal'>
                                        {itemsCost.map((elem, index) => (
                                            <Grid container spacing={2} key={index}>
                                                <Grid item xs={12}>
                                                    <Typography>
                                                        Materiais {index + 1}
                                                    </Typography>
                                                    <Typography variant='caption' color={'grey'}>
                                                        <strong>Nota: </strong>Se um dos campos não for preenchido, nenhum dado será guardado
                                                    </Typography>
                                                </Grid>
                                                <Grid item xs={12} md={3}>
                                                    <TextField
                                                        fullWidth
                                                        label={`Material ${index + 1}`}
                                                        name="title"
                                                        value={Object.keys(elem)[0] || ''}
                                                        onChange={(event) => handleInputChange(index, event)}
                                                        error={Boolean(titleErrors[index])}  // Mark input as error if there's a duplicate
                                                        helperText={titleErrors[index] || ''}  // Show error message if duplicate
                                                    />
                                                </Grid>
                                                <Grid item xs={12} md={9}>
                                                    <TextField
                                                        fullWidth
                                                        label={`Custo ${index + 1}`}
                                                        name="description"
                                                        value={Object.values(elem)[0] || ''}
                                                        onChange={(event) => handleInputChange(index, event)}
                                                    />
                                                </Grid>
                                                <Grid item xs={12}>
                                                    <Button
                                                        variant="contained"
                                                        color="error"
                                                        onClick={() => removeItemsCost(index)}
                                                        disabled={itemsCost.length < 2}
                                                        sx={{ marginBottom: 2 }}
                                                        startIcon={<Remove />}
                                                    >
                                                        Remover
                                                    </Button>
                                                </Grid>
                                            </Grid>
                                        ))}
                                        <Button onClick={addItemsCost} startIcon={<Add />}>
                                            Adicionar Dados
                                        </Button>
                                    </FormControl>
                                    <br />

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
                                        error={Boolean(errors.total_cost)}
                                        helperText={errors.total_cost && <InputError message={errors.total_cost} />}
                                        margin="normal"
                                    />

                                    <Button variant="outlined" type="submit" disabled={processing}>
                                        Submeter
                                    </Button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}