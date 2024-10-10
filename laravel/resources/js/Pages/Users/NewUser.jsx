import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import { Head, useForm } from '@inertiajs/react';
import { Button, TextField } from '@mui/material';

export default function Register({auth}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('users.create'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Utilizador</h2>}
        >
            
            {<Head title='Criar Utilizador' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>
                            <form onSubmit={submit}>
                                <TextField
                                    id="name"
                                    name="name"
                                    label="Nome"
                                    value={data.name}
                                    fullWidth
                                    margin="normal"
                                    isFocused={true}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={Boolean(errors.name)}
                                    helperText={errors.name  && <InputError message={errors.name} /> }
                                />

                                <TextField
                                    id="email"
                                    type="email"
                                    name="email"
                                    label="Email"
                                    value={data.email}
                                    fullWidth
                                    margin="normal"
                                    onChange={(e) => setData('email', e.target.value)}
                                    error={Boolean(errors.email)}
                                    helperText={errors.email}
                                />

                                <TextField
                                    id="phone"
                                    type="tel"
                                    name="phone"
                                    label="Número de Telemóvel"
                                    value={data.phone}
                                    fullWidth
                                    margin="normal"
                                    onChange={(e) => setData('phone', e.target.value)}
                                    error={Boolean(errors.phone)}
                                    helperText={errors.phone}
                                />
                                
                                <TextField
                                    id="password"
                                    type="password"
                                    name="password"
                                    label="Password"
                                    value={data.password}
                                    fullWidth
                                    margin="normal"
                                    onChange={(e) => setData('password', e.target.value)}
                                    error={Boolean(errors.password)}
                                    helperText={errors.password}
                                />

                                <TextField
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    label="Confirmar Password"
                                    value={data.password_confirmation}
                                    fullWidth
                                    margin="normal"
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    error={Boolean(errors.password_confirmation)}
                                    helperText={errors.password_confirmation}
                                />

                                <Button
                                    variant="outlined"
                                    type="submit"
                                    disabled={processing}
                                    sx={{ mt: 2 }}
                                >
                                    Submeter
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
