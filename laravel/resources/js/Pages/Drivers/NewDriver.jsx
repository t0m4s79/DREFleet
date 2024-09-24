import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, Button, RadioGroup, FormControl, FormControlLabel, Radio, TextField, Typography } from '@mui/material';

export default function NewDriver( {auth, users} ) {

    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        user_id: '',
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