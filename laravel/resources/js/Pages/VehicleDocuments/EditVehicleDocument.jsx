import React, { useState } from 'react';
import { debounce } from 'lodash';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputLabel from '@/Components/InputLabel';
import { TextField, Grid, Button, Autocomplete, FormControl, Typography } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';
import { Add, Remove } from '@mui/icons-material';
import { useEffect } from 'react';

{/*TODO: VEHICLE IMAGE SELECT */}
export default function EditVehicleDocument({ auth, vehicleDocument, vehicles}) {

    const [isEditMode, setisEditMode] = useState(false)
    const [documents, setDocuments] = useState([{}]); // Empty object for dynamic keys
    const [titleErrors, setTitleErrors] = useState([]);

    const initialData = {
        name: vehicleDocument.name,
        issue_date: vehicleDocument.issue_date,
        expiration_date: vehicleDocument.expiration_date,
        vehicle_id: vehicleDocument.vehicle_id,
        data: vehicleDocument.data,
    }

    const { data, setData, put, processing, errors } = useForm({...initialData});

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const vehicleList = vehicles.map((vehicle) => {
        return {value: vehicle.id, label: `#${vehicle.id} - ${vehicle.make} ${vehicle.model}, ${vehicle.license_plate}`}
    })

    // Transform the data object into an array of { title: description } objects
    const initialDocuments = Object.entries(vehicleDocument.data).map(([key, value]) => ({ [key]: value }));
    
    useEffect(() => {
        if (vehicleDocument?.data) {
            console.log('vehicleDocument?.data', vehicleDocument?.data)
            // Transform the data object into an array of { title: description } objects
            //const initialDocuments = Object.entries(vehicleDocument.data).map(([key, value]) => ({ [key]: value }));
            setDocuments(initialDocuments);
        }
    }, []);

    const toggleEdit = () => {
        if (isEditMode) {
            setDocuments(initialDocuments)
            setData({ ...initialData });  // Reset to initial values if canceling edit
        }
        setisEditMode(!isEditMode);
    }

    const debouncedSetData = debounce((key, value) => {
        setData(key, value);
    }, 300);

    // Handle dynamic input for title and description
    const handleInputChange = (index, event) => {
        const newDocuments = [...documents];
        const currentKey = Object.keys(newDocuments[index])[0];
        const currentValue = Object.values(newDocuments[index])[0];
        
        const newTitleErrors = [...titleErrors];
    
        if (event.target.name === 'title') {
            const newTitle = event.target.value;
            const isDuplicate = newDocuments.some((doc, idx) => Object.keys(doc)[0] === newTitle && idx !== index);
    
            if (isDuplicate) {
                newTitleErrors[index] = "Este título já existe.";
            } else {
                newTitleErrors[index] = null;
            }
    
            newDocuments[index] = { [newTitle]: currentValue || '' };
            setTitleErrors(newTitleErrors);
        } else if (event.target.name === 'description') {
            newDocuments[index] = { [currentKey || '']: event.target.value };
        }
    
        setDocuments(newDocuments);
    
        // Only debounce the internal state update, not the submission
        const flattenedData = newDocuments.reduce((acc, doc) => {
            const key = Object.keys(doc)[0];
            const value = doc[key];
    
            if (key && value) {
                acc[key] = value;
            }
    
            return acc;
        }, {});
    
        // Use debounced setData for typing
        debouncedSetData('data', flattenedData);
    };    
    
    const addDocument = () => {
        setDocuments([...documents, {}]);  // Add an empty object for a new key-value input
    };

    const removeDocument = (index) => {
        const newDocuments = [...documents];
        newDocuments.splice(index, 1);
        setDocuments(newDocuments);
        setData('data', newDocuments);
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        // Flatten the most recent documents before submission
        const flattenedData = documents.reduce((acc, doc) => {
            const key = Object.keys(doc)[0];
            const value = doc[key];

            if (key && value) {
                acc[key] = value;
            }

            return acc;
        }, {});

        // Set the final flattened data just before submit to avoid async issues
        setData('data', flattenedData);
        put(route('vehicleDocuments.edit', vehicleDocument.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Documento #{vehicleDocument.id}</h2>}
        >

            {<Head title='Editar Documento' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit} noValidate>
                            <input type="hidden" name="_token" value={csrfToken} />

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
                                    variant="outlined"
                                    color="error"
                                    disabled={processing}
                                    onClick={toggleEdit}
                                >
                                    Cancelar Edição
                                </Button>
                                <Button
                                    type="submit"
                                    variant="outlined"
                                    color="primary"
                                    disabled={processing}
                                >
                                    Submeter
                                </Button>
                            </div>)}
                            
                            <TextField
                                    fullWidth
                                    label="Nome"
                                    id="name"
                                    name="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={Boolean(errors.name)}
                                    helperText={errors.name && <InputError message={errors.name} />}
                                    margin="normal"
                                    disabled={!isEditMode}
                                />

                                <Grid container spacing={3}>
                                    <Grid item xs={6}>
                                        <InputLabel htmlFor="issue_date" value="Data de Emissão" />
                                        <TextField
                                            id='issue_date'
                                            name='issue_date'
                                            type="date"
                                            fullWidth
                                            value={data.issue_date}
                                            onChange={(e) => setData('issue_date', e.target.value)}
                                            error={errors.issue_date}
                                            helperText={errors.issue_date}
                                            sx={{ mb: 2 }}
                                            disabled={!isEditMode}
                                        />
                                    </Grid>

                                    <Grid item xs={6}>
                                        <InputLabel htmlFor="expiration_date" value="Data de Validade" />
                                        <TextField
                                            id='expiration_date'
                                            name='expiration_date'
                                            type="date"
                                            fullWidth
                                            value={data.expiration_date}
                                            onChange={(e) => setData('expiration_date', e.target.value)}
                                            error={errors.expiration_date}
                                            helperText={errors.expiration_date}
                                            sx={{ mb: 2 }}
                                            disabled={!isEditMode}
                                        />
                                    </Grid>
                                </Grid>

                                <br />

                                <Autocomplete
                                    id="vehicle"
                                    options={vehicleList}
                                    getOptionLabel={(option) => option.label}
                                    value={vehicleList.find(vehicle => vehicle.value === data.vehicle_id) || null}
                                    onChange={(e,value) => setData('vehicle_id', value.value)}
                                    disabled={!isEditMode}
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

                                <FormControl fullWidth margin='normal'>
                                    {documents.map((elem, index) => (
                                        <Grid container spacing={2} key={index}>
                                            <Grid item xs={12}>
                                                <Typography>
                                                    Dados Adicionais {index + 1}
                                                </Typography>
                                                <Typography variant='caption' color={'grey'}>
                                                    <strong>Nota: </strong>Se um dos campos não for preenchido, nenhum dado será guardado
                                                </Typography>
                                            </Grid>
                                            <Grid item xs={12} md={3}>
                                                <TextField 
                                                    fullWidth
                                                    label={`Título ${index + 1}`}
                                                    name="title"
                                                    value={Object.keys(elem) || ''}
                                                    onChange={(event) => handleInputChange(index, event)}
                                                    disabled={!isEditMode}
                                                    error={Boolean(titleErrors[index])}  // Mark input as error if there's a duplicate
                                                    helperText={titleErrors[index] || ''}  // Show error message if duplicate
                                                />
                                            </Grid>
                                            <Grid item xs={12} md={9}>
                                                <TextField 
                                                    fullWidth
                                                    label={`Descrição ${index + 1}`}
                                                    name="description"
                                                    value={Object.values(elem) || ''}
                                                    onChange={(event) => handleInputChange(index, event)}
                                                    disabled={!isEditMode}
                                                />
                                            </Grid>
                                            <Grid item xs={12}>
                                                <Button 
                                                    variant="outlined"
                                                    color="error" 
                                                    onClick={() => removeDocument(index)} 
                                                    disabled={!isEditMode && documents.length < 2}
                                                    sx={{ marginBottom: 2 }}
                                                    startIcon={<Remove />}
                                                >
                                                    Remover
                                                </Button>
                                            </Grid>
                                        </Grid>
                                    ))}
                                    <Button onClick={addDocument} startIcon={<Add />} disabled={!isEditMode}>
                                        Adicionar Dados
                                    </Button>
                                </FormControl>
                                <br/>
                            </form>
                        </div>
                    </div>
                </div>
        </AuthenticatedLayout>
    )
}