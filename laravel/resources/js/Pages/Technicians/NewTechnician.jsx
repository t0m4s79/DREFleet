import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, TextField, Button, Checkbox, ListItemText, MenuItem, OutlinedInput, Select, FormControl, InputLabel, Snackbar, Alert } from '@mui/material';
import { Head, useState, useEffect } from 'react';

export default function NewTechnician( {auth, users, flash} ) {

    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        id: '',
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    //console.log('users', users)

    const userList = users.map((user) => {
        return {value: user.id, label: `#${user.id} - ${user.name}`, }
    })

    // Handle Autocomplete selection
    const handleUserChange = (event, newValue) => {
        setData('id', newValue?.value.toString() || ''); // Update form data with the selected user's ID
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('technicians.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Técnico</h2>}
        >
            
            {<Head title='Criar Técnico' />}

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar técnico a partir de utilizador existente</h2>
                            
                            <form onSubmit={handleSubmit}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <p>Selecione o utilizador</p>

                                <Autocomplete
                                    id='user-combo-box'
                                    options={userList}
                                    getOptionLabel={(option) => option.label}
                                    onChange={handleUserChange}
                                    renderInput={(params) => <TextField {...params} label="Utilizador" />}
                                    sx={{ width: 500 }}
                                />
                                {errors.id && <InputError message={errors.id} />}
                                {/* <select name="id" id="">
                                    {user}
                                </select> */}
                                <br/>

                                <Button variant="outlined" type="submit" value="Submit">Submeter</Button>
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
            </div>
        
        </AuthenticatedLayout>
    )

}