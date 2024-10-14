import React from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Button, Select, MenuItem, InputLabel, FormControl } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';

export default function EditUser({ auth, user }) {

    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        phone: user.phone,
        status: user.status,
    });

    // Handle form input changes and update data using setData
    const handleChange = (e) => {
        const { name, value } = e.target;
        setData(name, value);
    };

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('users.edit',user.id)); // Submits the form to the specified endpoint
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Utilizador #{user.id}</h2>}
        >

            {<Head title='Editar Utilizador' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />
                            {/* Name Field */}
                            <TextField
                                label="Nome"
                                variant="outlined"
                                fullWidth
                                margin="normal"
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                error={Boolean(errors.name)}
                                helperText={errors.name}
                            />

                            {/* Email Field */}
                            <TextField
                                label="Email"
                                type="email"
                                variant="outlined"
                                fullWidth
                                margin="normal"
                                name="email"
                                value={data.email}
                                onChange={handleChange}
                                error={!!errors.email}
                                helperText={errors.email}
                            />

                            {/* Phone Field */}
                            <TextField
                                label="Número de telemóvel"
                                type="tel"
                                variant="outlined"
                                fullWidth
                                margin="normal"
                                name="phone"
                                value={data.phone}
                                onChange={handleChange}
                                error={!!errors.phone}
                                helperText={errors.phone}
                            />

                            {/* Status Select Field */}
                            <FormControl fullWidth margin="normal" error={!!errors.status}>
                                <InputLabel id="status-label">Estado</InputLabel>
                                <Select
                                    labelId="status-label"
                                    name="status"
                                    value={data.status}
                                    onChange={handleChange}
                                    label="Estado"
                                >
                                    <MenuItem value="Disponível">Disponível</MenuItem>
                                    <MenuItem value="Indisponível">Indisponível</MenuItem>
                                    <MenuItem value="Em Serviço">Em Serviço</MenuItem>
                                    <MenuItem value="Escondido">Escondido</MenuItem>
                                </Select>
                                {errors.status && <p style={{ color: 'red' }}>{errors.status}</p>}
                            </FormControl>

                            {/* Submit Button */}

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
        </AuthenticatedLayout>
    );
}