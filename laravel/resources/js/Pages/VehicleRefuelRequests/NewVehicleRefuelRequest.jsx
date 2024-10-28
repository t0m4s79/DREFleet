import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { Autocomplete, TextField, Button, FormControl, FormControlLabel, FormLabel, Grid, Radio, RadioGroup } from '@mui/material';

export default function NewVehicleRefuelRequest( {auth, vehicles} ) {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const { data, setData, post, errors, processing } = useForm({
        date: '',
        kilometrage: '',
        quantity: '',
        cost_per_unit: '',
        total_cost: '',
        fuel_type: '',
        vehicle_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicleRefuelRequests.create'));
    };

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Pedido de Reabastecimento de Veículo</h2>}
        >

            {<Head title='Pedido de Reabastecimento do Veículo' />}

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

                                        <InputLabel htmlFor="date" value="Data" />
                                        <TextField
                                            id='date'
                                            name='date'
                                            type="date"
                                            fullWidth
                                            value={data.date}
                                            onChange={(e) => setData('date', e.target.value)}
                                            error={errors.date}
                                            helperText={errors.date}
                                            sx={{ mb: 2 }}
                                        />

                                        <TextField
                                            label="Kilometragem"
                                            name="kilometrage"
                                            type="number"
                                            value={data.kilometrage}
                                            onChange={(e) => setData('kilometrage', e.target.value)}
                                            fullWidth
                                            margin="normal"
                                            inputProps={{ min: 0}}
                                            error={!!errors.kilometrage}
                                            helperText={errors.kilometrage}
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
                                                <FormControlLabel value="Elétrico" control={<Radio />} label="Elétrico" />
                                            </RadioGroup>
                                            {errors.fuel_type && <InputError message={errors.fuel_type} />}
                                        </FormControl>

                                        <Grid container spacing={2} alignItems="center">
                                            <Grid item xs={3}>
                                                <TextField
                                                    fullWidth
                                                    label="Quantidade"
                                                    id="quantity"
                                                    name="quantity"
                                                    type="number"
                                                    step=".01"
                                                    placeholder="0.00"
                                                    value={data.quantity}
                                                    onChange={(e) => setData('quantity', e.target.value)}
                                                    error={Boolean(errors.quantity)}
                                                    helperText={errors.quantity && <InputError message={errors.quantity} />}
                                                    margin="normal"
                                                />
                                            </Grid>

                                            <Grid item xs={1} style={{ textAlign: 'center' }}>
                                                x
                                            </Grid>

                                            <Grid item xs={3}>
                                                <TextField
                                                    fullWidth
                                                    label="Custo por unidade"
                                                    id="cost_per_unit"
                                                    name="cost_per_unit"
                                                    type="number"
                                                    step=".001"
                                                    placeholder="0.000"
                                                    value={data.cost_per_unit}
                                                    onChange={(e) => setData('cost_per_unit', e.target.value)}
                                                    error={Boolean(errors.cost_per_unit)}
                                                    helperText={errors.cost_per_unit && <InputError message={errors.cost_per_unit} />}
                                                    margin="normal"
                                                />
                                            </Grid>

                                            <Grid item xs={1} style={{ textAlign: 'center' }}>
                                                =
                                            </Grid>

                                            <Grid item xs={4}>
                                                <TextField
                                                    fullWidth
                                                    label="Custo total"
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
                                            </Grid>
                                        </Grid>

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