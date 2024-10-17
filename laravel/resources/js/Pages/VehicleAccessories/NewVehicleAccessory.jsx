import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import { Button, TextField, RadioGroup, FormControl, FormControlLabel, Radio, FormLabel, Autocomplete } from '@mui/material';

{/*TODO: Condition should turn grey and auto select expired if present date is bigger than expiration_date */}
export default function NewVehicleAccessory( {auth, vehicles} ) {

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        name: '',
        condition: '',
        expiration_date: '',
        vehicle_id: '',
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicleAccessories.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Acessório</h2>}
        >

            {<Head title='Criar Acessório' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar acessório de veículo</h2>
                            <form onSubmit={handleSubmit} id="newVehicleForm" noValidate>
                                <input type="hidden" name="_token" value={csrfToken} />

                                <TextField
                                    fullWidth
                                    label="Nome"
                                    id="name"
                                    name="name"
                                    value={data.make}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={Boolean(errors.name)}
                                    helperText={errors.name && <InputError message={errors.name} />}
                                    margin="normal"
                                />

                                <InputLabel htmlFor="expiration_date" value="Data de Validade" />
                                <TextField
                                    //label="Data e Hora de Início"
                                    id='expiration_date'
                                    name='expiration_date'
                                    type="date"
                                    fullWidth
                                    value={data.expiration_date}
                                    onChange={(e) => setData('expiration_date', e.target.value)}
                                    error={errors.expected_begin_date}
                                    helperText={errors.expected_begin_date}
                                    sx={{ mb: 2 }}
                                />
                                {errors.expiration_date && <InputError message={errors.expiration_date} />}


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

                                <br />

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

                                <Button variant="outlined" type="submit" disabled={processing}>
                                    Submeter
                                </Button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )

}