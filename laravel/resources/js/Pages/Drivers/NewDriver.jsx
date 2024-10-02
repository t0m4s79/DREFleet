import InputError from '@/Components/InputError';
import LicenseNumberInput from '@/Components/LicenseNumberInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, Button, RadioGroup, FormControl, FormControlLabel, Radio, TextField, Typography, Grid } from '@mui/material';
import { useState } from 'react';

{/*TODO: HEAVY LICENSE AND LICENSE TYPE NEXT TO EACH OTHER*/}
{/*TODO: IMPROVE LICENSE NUMBER FIELDS LOOK*/}
{/*TODO: LICENSE NUMBER FIELDS ERROR POSITIONS*/}
{/*TODO: SPACE BETWEEN MIDDLE AND LAST DIGITS*/}
export default function NewDriver( {auth, users} ) {

    //const [license, setLicense] = useState('');

    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        user_id: '',
        license: '',
        heavy_license: '',
        heavy_license_type: '',
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    //console.log('users', users)
    // Change how data is shown in the options
    const userList = users.map((user) => {
        return {value: user.id, label: `#${user.id} - ${user.name}`, }
    })

    // Handle Autocomplete selection
    const handleUserChange = (event, newValue) => {
        setData('user_id', newValue?.value.toString() || ''); // Update form data with the selected user's ID
    };

    const handleLicenseChange = (license) => {
        console.log('license', license)
        setData('license', license)
    };

    const handleHeavyChange = () => {
        if(data.heavy_license != 1)
            setData('heavy_license_type', null)
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        handleHeavyChange();
        post(route('drivers.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Condutor</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar condutor a partir de utilizador existente</h2>
                            
                            <form onSubmit={handleSubmit}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <p>Selecione o utilizador</p>

                                <Autocomplete
                                    id="user-combo-box"
                                    options={userList}
                                    getOptionLabel={(option) => option.label}
                                    onChange={handleUserChange}
                                    renderInput={(params) => (
                                        <TextField
                                            {...params}
                                            label="Utilizador"
                                            variant="outlined"
                                            margin="normal"
                                            fullWidth
                                            error={!!errors.user_id}
                                            helperText={errors.user_id}
                                        />
                                    )}
                                    sx={{ width: 500, marginBottom: 2 }}
                                />

                                {/* <Typography variant="body1">Número da Carta de Condução</Typography>
                                <Grid container>
                                    <TextField
                                        type="text"
                                        id="license_region_identifier"
                                        label="Identificador da Região"
                                        name="license_region_identifier"
                                        value={data.license_region_identifier}
                                        onChange={(e) => setData('license_region_identifier', e.target.value)}
                                        maxLength={2}
                                    />
                                    {errors.license_region_identifier && (
                                        <InputError message={errors.license_region_identifier} />
                                    )}
                                    
                                    <span>-</span>

                                    <TextField
                                        type="text"
                                        label="Dígitos Intermédios"
                                        id="license_middle_digits"
                                        name="license_middle_digits"
                                        value={data.license_middle_digits}
                                        onChange={(e) => setData('license_middle_digits', e.target.value)}
                                    />
                                    {errors.license_middle_digits && (
                                        <InputError message={errors.license_middle_digits} />
                                    )}
                                    
                                    <span> </span>

                                    <TextField
                                        type="text"
                                        label="Dígito Final"
                                        id="license_last_digit"
                                        name="license_last_digit"
                                        value={data.license_last_digit}
                                        onChange={(e) => setData('license_last_digit', e.target.value)}
                                    />
                                    {errors.license_last_digit && (
                                        <InputError message={errors.license_last_digit} />
                                    )}
                                </Grid> */}

                                <LicenseNumberInput value={data.license} onChange={handleLicenseChange} />

                                <Typography variant="body1">Carta de Pesados</Typography>
                                {/* Radio buttons for heavy_license */}
                                <FormControl component="fieldset">
                                    <RadioGroup
                                        aria-label="heavy_license"
                                        name="heavy_license"
                                        value={data.heavy_license}
                                        onChange={(e) => setData('heavy_license', e.target.value)}
                                    >
                                        <FormControlLabel
                                            value="0"
                                            control={<Radio />}
                                            label="Não"
                                        />
                                        <FormControlLabel
                                            value="1"
                                            control={<Radio />}
                                            label="Sim"
                                        />
                                    </RadioGroup>
                                </FormControl>
                                {errors.heavy_license && (
                                    <InputError message={errors.heavy_license} />
                                )}

                            <Typography variant="body1">Tipo de Carta de Pesados</Typography>
                                {/* Radio buttons for heavy_license_type */}
                                <FormControl component="fieldset" disabled={data.heavy_license == '0'}>
                                    <RadioGroup
                                        aria-label="heavy_license_type"
                                        name="heavy_license_type"
                                        value={data.heavy_license_type}
                                        onChange={(e) => setData('heavy_license_type', e.target.value)}
                                    >
                                        <FormControlLabel
                                            value="Mercadorias"
                                            control={<Radio />}
                                            label="Mercadorias"
                                        />
                                        <FormControlLabel
                                            value="Passageiros"
                                            control={<Radio />}
                                            label="Passageiros"
                                        />
                                    </RadioGroup>
                                </FormControl>
                                {errors.heavy_license_type && (
                                    <InputError message={errors.heavy_license_type} />
                                )}

                                <br />

                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-gray-600">Guardado</p>
                                </Transition>
                            </form>
            
                        </div>
                    </div>
                </div>
            </div>
        
        </AuthenticatedLayout>
    )

}