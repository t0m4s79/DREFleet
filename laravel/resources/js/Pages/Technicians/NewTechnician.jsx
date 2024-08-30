import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, TextField, Button, Checkbox, ListItemText, MenuItem, OutlinedInput, Select, FormControl, InputLabel, Snackbar, Alert } from '@mui/material';
import { useState, useEffect } from 'react';

export default function NewTechnician( {auth, users, priority1AvailableKids,priority2AvailableKids, flash} ) {

    const [listKids1, setListKids1] = useState([])
    const [listKids2, setListKids2] = useState([])

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
        kidsList1: '',
        kidsList2: '',
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const userList = users.map((user) => {
        return {value: user.id, label: `#${user.id} - ${user.name}`, }
    })

    const kidList1 = priority1AvailableKids.map((kid) => {
        return {value: kid.id, label: `#${kid.id} - ${kid.name}`, }
    })

    const kidList2 = priority2AvailableKids.map((kid) => {
        return {value: kid.id, label: `#${kid.id} - ${kid.name}`, }
    })

    // Handle Autocomplete selection
    const handleUserChange = (event, newValue) => {
        setData('id', newValue?.value.toString() || ''); // Update form data with the selected user's ID
    };

    const handleKid1Change =(event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;

        setListKids1(selectedKids);
        setData('kidsList1', selectedKids);
    };

    const handleKid2Change =(event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;

        setListKids2(selectedKids);
        setData('kidsList2', selectedKids);
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

                                
                                {errors.heavy_license && <InputError message={errors.heavy_license} />}

                                <p>Crianças pelo qual é responsável</p><br />

                                <p>Prioridade 1</p>
                                <FormControl sx={{ minWidth: 300 }} margin="normal">
                                    <InputLabel id="kids-1">Adicionar Criança</InputLabel>
                                    <Select
                                        labelId="kids-1"
                                        multiple
                                        value={listKids1}
                                        onChange={handleKid1Change}
                                        input={<OutlinedInput label="Adicionar Criança" />}
                                        renderValue={(selected) => selected.join(', ')}
                                        sx={{ maxHeight: '200px', width: '100%' }}
                                    >
                                        {kidList1.map((kid) => (
                                            <MenuItem key={kid.value} value={kid.value}>
                                                <Checkbox checked={listKids1.indexOf(kid.value) > -1} />
                                                <ListItemText primary={kid.label} />
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                                <br/><br />

                                <p>Prioridade 2</p>
                                <FormControl sx={{ minWidth: 300 }} margin="normal">
                                    <InputLabel id="kids-2">Adicionar Criança</InputLabel>
                                    <Select
                                        labelId="kids-2"
                                        multiple
                                        value={listKids2}
                                        onChange={handleKid2Change}
                                        input={<OutlinedInput label="Adicionar Criança" />}
                                        renderValue={(selected) => selected.join(', ')}
                                        sx={{ maxHeight: '200px', width: '100%' }}
                                    >
                                        {kidList2.map((kid) => (
                                            <MenuItem key={kid.value} value={kid.value}>
                                                <Checkbox checked={listKids2.indexOf(kid.value) > -1} />
                                                <ListItemText primary={kid.label} />
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
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