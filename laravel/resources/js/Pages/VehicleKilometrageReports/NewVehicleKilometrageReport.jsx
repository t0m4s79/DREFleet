import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { TextField, Button, Grid, Autocomplete } from '@mui/material';

export default function NewVehicleKilometrageReports( {auth, vehicles, drivers} ) {


    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const { data, setData, post, errors, processing } = useForm({
        date: '',
        begin_kilometrage: '',
        end_kilometrage: '',
        vehicle_id: '',
        driver_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicleKilometrageReports.create'));
    };

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    const driversList = drivers.map((driver) => {
        return {value: driver.user_id, label: `#${driver.user_id} - ${driver.name} ${driver.license_number}`}
    })

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Entrada de Kilometragem de Veículo</h2>}
        >

            {<Head title='Registo de Kilometragem do Veículo' />}

            <div className='py-12'>
                    <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className='p-6'>
                                <div className='my-6'>                                    
                                    <form onSubmit={handleSubmit}>
                                        <input type="hidden" name="_token" value={csrfToken} />

                                        <Grid container spacing={3}>
                                            <Grid item xs={6}>
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
                                            </Grid>

                                            <Grid item xs={6}>
                                                <Autocomplete
                                                    id="driver"
                                                    options={driversList}
                                                    getOptionLabel={(option) => option.label}
                                                    onChange={(e,value) => setData('driver_id', value.value)}
                                                    renderInput={(params) => (
                                                        <TextField
                                                            {...params}
                                                            label="Condutor"
                                                            fullWidth
                                                            value={data.driver_id}
                                                            error={errors.driver_id}
                                                            helperText={errors.driver_id}
                                                        />
                                                    )}
                                                    sx={{ mb: 2 }}
                                                />
                                            </Grid>
                                        </Grid>

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

                                        <Grid container spacing={3}>
                                            <Grid item xs={6}>
                                                <TextField
                                                    label="Kilometragem Inicial"
                                                    name="begin_kilometrage"
                                                    type="number"
                                                    value={data.begin_kilometrage}
                                                    onChange={(e) => setData('begin_kilometrage', e.target.value)}
                                                    fullWidth
                                                    margin="normal"
                                                    inputProps={{ min: 0}}
                                                    error={!!errors.begin_kilometrage}
                                                    helperText={errors.begin_kilometrage}
                                                />      
                                            </Grid>

                                            <Grid item xs={6}>
                                                <TextField
                                                    label="Kilometragem Final"
                                                    name="end_kilometrage"
                                                    type="number"
                                                    value={data.end_kilometrage}
                                                    onChange={(e) => setData('end_kilometrage', e.target.value)}
                                                    fullWidth
                                                    margin="normal"
                                                    inputProps={{ min: 0}}
                                                    error={!!errors.end_kilometrage}
                                                    helperText={errors.end_kilometrage}
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