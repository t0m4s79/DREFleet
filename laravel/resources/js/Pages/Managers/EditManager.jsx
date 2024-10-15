import React, { useState, useEffect } from 'react';
import { Snackbar, Alert, Checkbox, FormControl, InputLabel, ListItemText, MenuItem, OutlinedInput, Select, Button, TextField, FormControlLabel, Radio, RadioGroup, FormLabel } from '@mui/material';import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, useForm } from '@inertiajs/react';

export default function EditManager({ auth, manager, flash}) {
    
    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'
    const [isEditMode, setisEditMode] = useState(false)

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    const { data, setData, put, errors, processing } = useForm({
        id: manager.id,
        name: manager.name,
        email: manager.email,
        phone: manager.phone,
        status: manager.status,
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
    };

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('managers.edit', manager.id), {
            onError: (error) => {
                // Handle errors, display them if necessary
                console.error(error);
            }
        }); // Use the Inertia put method to submit form data
    };

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Gestor #{manager.id}</h2>}
        >

            {<Head title='Editar Gestor' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

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
                            </div>)}

                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />

                            {/* Name Field */}
                            <FormControl fullWidth margin="normal">
                                <TextField
                                    label="Nome"
                                    id="name"
                                    name="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className={!isEditMode ? 'read-only-field' : ''}
                                    disabled={!isEditMode}
                                    error={errors.name ? true : false}
                                    helperText={errors.name && errors.name}
                                    fullWidth
                                />
                            </FormControl>

                            {/* Email Field */}
                            <FormControl fullWidth margin="normal">
                                <TextField
                                    label="Email"
                                    id="email"
                                    name="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={!isEditMode ? 'read-only-field' : ''}
                                    disabled={!isEditMode}
                                    error={errors.email ? true : false}
                                    helperText={errors.email && errors.email}
                                    fullWidth
                                />
                            </FormControl>

                            {/* Phone Field */}
                            <FormControl fullWidth margin="normal">
                                <TextField
                                    label="Número de telemóvel"
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    className={!isEditMode ? 'read-only-field' : ''}
                                    disabled={!isEditMode}
                                    error={errors.phone ? true : false}
                                    helperText={errors.phone && errors.phone}
                                    fullWidth
                                />
                            </FormControl>

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                <FormLabel component="legend">Disponível?</FormLabel>
                                <RadioGroup
                                    name="status"
                                    value={data.status}
                                    onChange={handleChange}
                                    row
                                >
                                    <FormControlLabel
                                        value="Disponível"
                                        control={<Radio />}
                                        label="Disponível"
                                    />
                                    <FormControlLabel
                                        value="Indisponível"
                                        control={<Radio />}
                                        label="Indisponível"
                                    />
                                    <FormControlLabel
                                        value="Em Serviço"
                                        control={<Radio />}
                                        label="Em Serviço"
                                    />
                                    <FormControlLabel
                                        value="Escondido"
                                        control={<Radio />}
                                        label="Escondido"
                                    />
                                </RadioGroup>
                            </FormControl>
                            <br/>

                            {/* Submit Button */}
                        </form>


                        <Snackbar 
                                open={openSnackbar} 
                                autoHideDuration={3000}
                                onClose={() => setOpenSnackbar(false)}
                                anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}
                            >
                                <Alert variant='filled' onClose={() => setOpenSnackbar(false)} severity={snackbarSeverity} sx={{ width: '100%' }}>
                                    {snackbarMessage}
                                </Alert>
                        </Snackbar>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}