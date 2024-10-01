import { Head, Link, useForm } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';
import { TextField, Button, Grid, Autocomplete } from '@mui/material';
import InputLabel from '@/Components/InputLabel';
import { useState } from 'react';


export default function EditOrder({auth, order, drivers, vehicles, technicians, managers, kids, places}) {
    console.log('order', order);
    // console.log(drivers);
    // console.log(vehicles);
    // console.log(technicians);
    // console.log(managers);
    // console.log(kids);
    // console.log(places)

    // Deconstruct places to change label display
    const placesList = places.map((place) => {
        return {value: place.id, label: `#${place.id} - ${place.address}`}
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const { data, setData, put, errors, processing} = useForm({
        begin_address: order.begin_address,
        begin_latitude: order.begin_latitude,
        begin_longitude: order.begin_longitude,
        end_address: order.end_address,
        end_latitude: order.end_latitude,
        end_longitude: order.end_longitude,
        begin_date: order.begin_date,
        end_date: order.end_date,
        vehicle_id: order.vehicle_id,
        driver_id: order.driver_id,
        technician_id: order.technician_id,
        trajectory: order.trajectory
    })

    const formatPlaceInfo = (place) => {
        return {value: place.id, label: `#${place.id} - ${place.address}`}
    }

    const beginAddress = formatPlaceInfo(order.begin_address);
    console.log('beginAddress', beginAddress)
    const endAddress = formatPlaceInfo(order.end_address);
    console.log('endAddres', endAddress)

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('orders.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pedido #{order.id}</h2>}
        >

            <Head title="Pedidos" />

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>
                            <div className='my-6'>    
                                <LeafletMap routing={true} onTrajectoryChange={(trajectory) => document.getElementById('trajectory').value = JSON.stringify(trajectory)} />
                            </div>

                            <form onSubmit={handleSubmit}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="hidden" name="_method" value="PUT" />

                                <Grid container spacing={3}>
                                    {/* Begin Address */}
                                    <Grid item xs={12}>
                                        <InputLabel htmlFor='morada-origem' value={'Morada da Origem'}/>
                                        <Autocomplete
                                            label="Morada da Origem"
                                            id='morada-origem'
                                            name='morada-origem'
                                            options={placesList}
                                            getOptionLabel={(option) => option.label}
                                            onChange={(e) => setData('begin_address', e.target.value)}
                                            renderInput={(params) => (
                                                <TextField
                                                    {...params}
                                                    fullWidth
                                                    value={beginAddress.value}
                                                    error={errors.begin_address ? true : false}
                                                    helperText={errors.begin_address}
                                                />
                                            )}
                                        />
                                    </Grid>

                                    {/* Begin Latitude */}
                                    <Grid item xs={6}>
                                        <TextField
                                            label="Latitude da Origem"
                                            id='latitude-origem'
                                            name='latitude-origem'
                                            type="number"
                                            fullWidth
                                            inputProps={{ step: '0.0000000001', min: '-90', max: '90' }}
                                            value={data.begin_latitude}
                                            onChange={(e) => setData('begin_latitude', e.target.value)}
                                            error={errors.begin_latitude ? true : false}
                                            helperText={errors.begin_latitude}
                                        />
                                    </Grid>

                                    {/* Begin Longitude */}
                                    <Grid item xs={6}>
                                        <TextField
                                            label="Longitude da Origem"
                                            id='longitude-origem'
                                            name='longitude-origem'
                                            type="number"
                                            fullWidth
                                            inputProps={{ step: '0.0000000001', min: '-180', max: '180' }}
                                            value={data.begin_longitude}
                                            onChange={(e) => setData('begin_longitude', e.target.value)}
                                            error={errors.begin_longitude ? true : false}
                                            helperText={errors.begin_longitude}
                                        />
                                    </Grid>

                                    {/* End Address */}
                                    <Grid item xs={12}>
                                        <InputLabel htmlFor='morada-destino' value={'Morada de Destino'}/>
                                        <Autocomplete
                                            label="Morada de Destino"
                                            id='morada-destino'
                                            name='morada-destino'
                                            options={placesList}
                                            getOptionLabel={(option) => option.label}
                                            onChange={(e) => setData('end_address', e.target.value)}
                                            renderInput={(params) => (
                                                <TextField
                                                    {...params}
                                                    fullWidth
                                                    value={endAddress.value}
                                                    error={errors.begin_address ? true : false}
                                                    helperText={errors.begin_address}
                                                />
                                            )}
                                        />
                                    </Grid>

                                    {/* End Latitude */}
                                    <Grid item xs={6}>
                                        <TextField
                                            label="Latitude do Destino"
                                            type="number"
                                            fullWidth
                                            inputProps={{ step: '0.0000000001', min: '-90', max: '90' }}
                                            value={data.end_latitude}
                                            onChange={(e) => setData('end_latitude', e.target.value)}
                                            error={errors.end_latitude ? true : false}
                                            helperText={errors.end_latitude}
                                        />
                                    </Grid>

                                    {/* End Longitude */}
                                    <Grid item xs={6}>
                                        <TextField
                                            label="Longitude do Destino"
                                            type="number"
                                            fullWidth
                                            inputProps={{ step: '0.0000000001', min: '-180', max: '180' }}
                                            value={data.end_longitude}
                                            onChange={(e) => setData('end_longitude', e.target.value)}
                                            error={errors.end_longitude ? true : false}
                                            helperText={errors.end_longitude}
                                        />
                                    </Grid>

                        <label htmlFor="planned_begin_date">Data e Hora de Início</label><br/>
                        <input type="datetime-local" id="planned_begin_date" name="planned_begin_date" value="2024-09-19T10:30"/><br/>

                        <label htmlFor="planned_end_date">Data e Hora de Fim</label><br />
                        <input type="datetime-local" id="planned_end_date" name="planned_end_date" value="2024-09-19T10:30"/><br/>

                                    {/* Vehicle */}
                                    <Grid item xs={12}>
                                        <TextField
                                            label="Veículo"
                                            fullWidth
                                            value={data.vehicle_id}
                                            onChange={(e) => setData('vehicle_id', e.target.value)}
                                            error={errors.vehicle_id ? true : false}
                                            helperText={errors.vehicle_id}
                                        />
                                    </Grid>

                                    {/* Driver */}
                                    <Grid item xs={12}>
                                        <TextField
                                            label="Condutor"
                                            fullWidth
                                            value={data.driver_id}
                                            onChange={(e) => setData('driver_id', e.target.value)}
                                            error={errors.driver_id ? true : false}
                                            helperText={errors.driver_id}
                                        />
                                    </Grid>

                                    {/* Technician */}
                                    <Grid item xs={12}>
                                        <TextField
                                            label="Técnico"
                                            fullWidth
                                            value={data.technician_id}
                                            onChange={(e) => setData('technician_id', e.target.value)}
                                            error={errors.technician_id ? true : false}
                                            helperText={errors.technician_id}
                                        />
                                    </Grid>

                                    {/* Submit Button */}
                                    <Grid item xs={12}>
                                        <Button type="submit" variant="outlined" color="primary" disabled={processing}>
                                            Submeter
                                        </Button>
                                    </Grid>
                                </Grid>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </AuthenticatedLayout>
    );
}