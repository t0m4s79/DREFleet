import React, { useState, useEffect } from 'react';
import { Snackbar, Alert, Checkbox, FormControl, InputLabel, ListItemText, MenuItem, OutlinedInput, Select, Button, TextField, FormControlLabel, Radio, RadioGroup, FormLabel } from '@mui/material';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm } from '@inertiajs/react';

export default function EditTechnician({ auth, technician, associatedKids, addPriority1, addPriority2, flash}) {
    
    const [openSnackbar, setOpenSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarSeverity, setSnackbarSeverity] = useState('success'); // 'success' or 'error'

    const [selectedAddKids1, setselectedAddKids1] = useState([]);
    const [selectedAddKids2, setselectedAddKids2] = useState([]);
    const [selectedRemKids1, setselectedRemKids1] = useState([]);
    const [selectedRemKids2, setselectedRemKids2] = useState([]);

    const [selectChangePriority, setSelectChangePriority] = useState([]);

    useEffect(() => {
        if (flash.message || flash.error) {
            setSnackbarMessage(flash.message || flash.error);
            setSnackbarSeverity(flash.error ? 'error' : 'success');
            setOpenSnackbar(true);
        }
    }, [flash]);

    const { data, setData, put, errors, processing } = useForm({
        id: technician.id,
        name: technician.name,
        email: technician.email,
        phone: technician.phone,
        status: technician.status,
        addPriority1: [],
        removePriority1: [],
        addPriority2: [],
        removePriority2: [],
        changePriority: [],
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setData(name, type === 'radio' ? value : value);
    };
    
    // Handle autocomplete section
    const handleAddPriority1Change = (event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;
        setselectedAddKids1(selectedKids);
        setData('addPriority1', selectedKids)
    };

    const handleAddPriority2Change = (event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;
        setselectedAddKids2(selectedKids);
        setData('addPriority2', selectedKids)
    };

    const handleRemovePriority1Change = (event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;
        setselectedRemKids1(selectedKids);
        setData('removePriority1', selectedKids)
    };

    const handleRemovePriority2Change = (event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;
        setselectedRemKids2(selectedKids);
        setData('removePriority2', selectedKids)
    };

    const handleChangePriority = (event) => {
        const {
            target: { value },
        } = event;

        const selectedKids = typeof value === 'string' ? value.split(',') : value;
        setSelectChangePriority(selectedKids);
        setData('changePriority', selectedKids)
    }

    //Split associatedKids array into 2 arrays, one for each kid priority
    const KidsPrio1 = associatedKids.filter((kid) => kid.priority == 1)
        .map((kid) => ({
            value: kid.id,
            label: `#${kid.id} - ${kid.name}`,
    }));
    console.log('KidsPrio1', KidsPrio1)

    const KidsPrio2 = associatedKids.filter((kid) => kid.priority == 2)
        .map((kid) => ({
            value: kid.id,
            label: `#${kid.id} - ${kid.name}`,
    }));;
    console.log('KidsPrio2', KidsPrio2)

    //Change how kids data is shown
    const kidList1 = addPriority1.map((kid) => ({
        value: kid.id,
        label: `#${kid.id} - ${kid.name}`,
    }));

    const kidList2 = addPriority2.map((kid) => ({
        value: kid.id,
        label: `#${kid.id} - ${kid.name}`,
    }));

    const changePrioKidsList = associatedKids.map((kid) => ({
        value: kid.id,
        label: `#${kid.id} - ${kid.name}`,
    }));

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('technicians.edit', technician.id), {
            onError: (error) => {
                // Handle errors, display them if necessary
                console.error(error);
            }
        }); // Use the Inertia put method to submit form data
    };

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Técnico #{technician.id}</h2>}
        >

            {/*<Head title={'Utilizador'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
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
                                    error={errors.phone ? true : false}
                                    helperText={errors.phone && errors.phone}
                                    fullWidth
                                />
                            </FormControl>

                            <FormControl component="fieldset" margin="normal">
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

                            {/* Priority 1 Kids Selection */}
                            <FormControl fullWidth margin="normal">
                                <InputLabel id="kids-add-1">Adicionar crianças prioritárias (Prioridade 1)</InputLabel>
                                <Select
                                    labelId="kids-add-1"
                                    multiple
                                    value={selectedAddKids1}
                                    onChange={handleAddPriority1Change}
                                    input={<OutlinedInput label="Adicionar crianças prioritárias (Prioridade 1)" />}
                                    renderValue={(selected) => selected.join(', ')}
                                    error={errors.kidsList1 ? true : false}
                                    sx={{ maxHeight: '200px', width: '100%' }}
                                >
                                    {kidList1.map((kid) => (
                                        <MenuItem key={kid.value} value={kid.value} disabled={selectedAddKids2.includes(kid.value)}>
                                            <Checkbox checked={selectedAddKids1.indexOf(kid.value) > -1} />
                                            <ListItemText primary={kid.label} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <FormControl fullWidth margin="normal">
                                <InputLabel id="kids-rem-1">Remover crianças prioritárias (Prioridade 1)</InputLabel>
                                <Select
                                    labelId="kids-rem-1"
                                    multiple
                                    value={selectedRemKids1}
                                    onChange={handleRemovePriority1Change}
                                    input={<OutlinedInput label="Remover crianças prioritárias (Prioridade 1)" />}
                                    renderValue={(selected) => selected.join(', ')}
                                    error={errors.kidsList1 ? true : false}
                                    sx={{ maxHeight: '200px', width: '100%' }}
                                >
                                    {KidsPrio1.map((kid) => (
                                        <MenuItem key={kid.value} value={kid.value} disabled={selectChangePriority.includes(kid.value)}>
                                            <Checkbox checked={selectedRemKids1.indexOf(kid.value) > -1} />
                                            <ListItemText primary={kid.label} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>


                            {/* Priority 2 Kids Selection */}
                            <FormControl fullWidth margin="normal">
                                <InputLabel id="kids-add-2">Adicionar crianças secundárias (Prioridade 2)</InputLabel>
                                <Select
                                    labelId="kids-add-2"
                                    multiple
                                    value={selectedAddKids2}
                                    onChange={handleAddPriority2Change}
                                    input={<OutlinedInput label="Adicionar crianças secundárias (Prioridade 2)" />}
                                    renderValue={(selected) => selected.join(', ')}
                                    error={errors.kidsList2 ? true : false}
                                    sx={{ maxHeight: '200px', width: '100%' }}
                                >
                                    {kidList2.map((kid) => (
                                        <MenuItem key={kid.value} value={kid.value} disabled={selectedAddKids1.includes(kid.value)}>
                                            <Checkbox checked={selectedAddKids2.indexOf(kid.value) > -1} />
                                            <ListItemText primary={kid.label} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <FormControl fullWidth margin="normal">
                                <InputLabel id="kids-rem-2">Remover crianças secundárias (Prioridade 2)</InputLabel>
                                <Select
                                    labelId="kids-rem-2"
                                    multiple
                                    value={selectedRemKids2}
                                    onChange={handleRemovePriority2Change}
                                    input={<OutlinedInput label="Adicionar crianças secundárias (Prioridade 2)" />}
                                    renderValue={(selected) => selected.join(', ')}
                                    error={errors.kidsList2 ? true : false}
                                    sx={{ maxHeight: '200px', width: '100%' }}
                                >
                                    {KidsPrio2.map((kid) => (
                                        <MenuItem key={kid.value} value={kid.value} disabled={selectChangePriority.includes(kid.value)}>
                                            <Checkbox checked={selectedRemKids2.indexOf(kid.value) > -1} />
                                            <ListItemText primary={kid.label} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>


                            <FormControl fullWidth margin="normal">
                                <InputLabel id="kids-prio-change">Alterar prioridade das crianças</InputLabel>
                                <Select
                                    labelId="kids-prio-change"
                                    multiple
                                    value={selectChangePriority}
                                    onChange={handleChangePriority}
                                    input={<OutlinedInput label="Alterar prioridade das crianças" />}
                                    renderValue={(selected) => selected.join(', ')}
                                    error={errors.kidsList2 ? true : false}
                                    sx={{ maxHeight: '200px', width: '100%' }}
                                >
                                    {changePrioKidsList.map((kid) => (
                                        <MenuItem key={kid.value} value={kid.value} disabled={selectedRemKids1.includes(kid.value) || selectedRemKids2.includes(kid.value)}>
                                            <Checkbox checked={selectChangePriority.indexOf(kid.value) > -1} />
                                            <ListItemText primary={kid.label} />
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <br />

                            {/* Submit Button */}
                            <Button type="submit" variant="contained" color="primary" disabled={processing}>
                                Submeter
                            </Button>
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