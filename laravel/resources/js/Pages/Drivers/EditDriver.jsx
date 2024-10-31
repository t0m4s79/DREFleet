import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { TextField, Button, FormControl, FormLabel, RadioGroup, FormControlLabel, Radio, Typography, Grid } from '@mui/material';
import { Head, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import LicenseNumberInput from '@/Components/LicenseNumberInput';

export default function EditDriver({ auth, driver }) {

    //console.log(driver)
    const [isEditMode, setisEditMode] = useState(false)
    // Inertia's built-in useForm hook to manage form data, actions, errors
    // Define data to be sent to the backend
    const { data, setData, put, errors, processing } = useForm({
        user_id: driver.user_id,
        name: driver.name,
        email: driver.email,
        phone: driver.phone,
        license_number: driver.license_number,
        heavy_license: driver.heavy_license,
        heavy_license_type: driver.heavy_license_type,
        license_expiration_date: driver.license_expiration_date,
        tcc: driver.tcc,
        tcc_expiration_date: driver.tcc_expiration_date,
        status: driver.status,
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

    const handleLicenseChange = (license) => {
        console.log('license', license)
        setData('license_number', license)
    };

    const handleHeavyChange = () => {
        if(data.heavy_license != 1)
            setData('heavy_license_type', null)
    }

    const handleTccChange = () => {
        if(data.tcc != 1)
            setData('tcc_expiration_date', null)
    }
    
    const handleSubmit = (e) => {
        e.preventDefault();
        handleHeavyChange();
        handleTccChange();
        put(route('drivers.edit', driver.user_id));
    };

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutor #{driver.user_id}</h2>}
        >

            {<Head title='Editar Condutor' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

                        <form onSubmit={handleSubmit}>
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="user_id" value={driver.user_id} />

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
                                label="Nome"
                                id="name"
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                variant="outlined"
                                error={Boolean(errors.name)}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                helperText={errors.name  && <InputError message={errors.name} /> }
                            />

                            <TextField
                                label="Email"
                                id="email"
                                name="email"
                                value={data.email}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                variant="outlined"
                                type="email"
                                error={Boolean(errors.email)}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                helperText={errors.email  && <InputError message={errors.email} />}
                            />

                            <TextField
                                label="Número de telemóvel"
                                id="phone"
                                name="phone"
                                value={data.phone}
                                onChange={handleChange}
                                fullWidth
                                margin="normal"
                                variant="outlined"
                                type="tel"
                                error={Boolean(errors.phone)}
                                className={!isEditMode ? 'read-only-field' : ''}
                                disabled={!isEditMode}
                                helperText={errors.phone && <InputError message={errors.phone} />}
                            />

                            <Grid container spacing={3}>
                                <Grid item xs={6}>
                                    <LicenseNumberInput value={data.license_number} onChange={handleLicenseChange} isDisabled={!isEditMode}/>
                                </Grid>

                                <Grid item xs={3} sx={{marginTop: 2}}>
                                <Typography>Data de Validade da Carta</Typography>
                                <TextField
                                    //label="Data e Hora de Início"
                                    id='license_expiration_date'
                                    name='license_expiration_date'
                                    type="date"
                                    fullWidth
                                    value={data.license_expiration_date}
                                    onChange={(e) => setData('license_expiration_date', e.target.value)}
                                    error={errors.license_expiration_date}
                                    helperText={errors.license_expiration_date}
                                    className={!isEditMode ? 'read-only-field' : ''}
                                    disabled={!isEditMode}
                                    sx={{ mb: 2 }}
                                />
                                </Grid>
                            </Grid>
                                
                            <Grid container spacing={3}>
                                <Grid item xs={6}>
                                    <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                        <FormLabel component="legend">Carta de Pesados</FormLabel>
                                        <RadioGroup
                                            name="heavy_license"
                                            value={data.heavy_license}
                                            onChange={handleChange}
                                            row
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
                                </Grid>
                                <br/>

                                <Grid item xs={6}>
                                    <FormControl component="fieldset" margin="normal" disabled={!isEditMode || data.heavy_license == '0'}>
                                        <FormLabel component="legend">Tipo de Carta de Pesados</FormLabel>
                                        <RadioGroup
                                            name="heavy_license_type"
                                            value={data.heavy_license_type}
                                            onChange={handleChange}
                                            row
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
                                </Grid>
                            </Grid>
                            <br/>

                            <Grid container spacing={3}>
                                <Grid item xs={6}>
                                    <Typography variant="body1">TCC</Typography>
                                    {/* Radio buttons for heavy_license */}
                                    <FormControl component="fieldset" disabled={!isEditMode}>
                                        <RadioGroup
                                            name="tcc"
                                            value={data.tcc}
                                            onChange={handleChange}
                                            row
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
                                    {errors.tcc && (
                                        <InputError message={errors.tcc} />
                                    )}                                    
                                </Grid>

                                <Grid item xs={3} sx={{marginTop: 2}}>
                                    <Typography>Data de Validade do TCC</Typography>
                                    <TextField
                                            //label="Data e Hora de Início"
                                            id='tcc_expiration_date'
                                            name='tcc_expiration_date'
                                            type="date"
                                            fullWidth
                                            value={data.tcc_expiration_date}
                                            onChange={handleChange}
                                            disabled={!isEditMode || data.tcc == '0'}
                                            error={errors.tcc_expiration_date}
                                            helperText={errors.tcc_expiration_date}
                                            sx={{ mb: 2 }}
                                        />
                                </Grid>
                            </Grid>

                            <FormControl component="fieldset" margin="normal" disabled={!isEditMode}>
                                <FormLabel component="legend">Estado</FormLabel>
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
                        </form>
                       
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}