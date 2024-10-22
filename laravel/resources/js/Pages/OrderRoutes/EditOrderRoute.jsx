import OrderRoutePolygon from '@/Components/OrderRoutePolygon';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Autocomplete, Button, Checkbox, Grid, TextField } from '@mui/material';
import { HexColorPicker, HexColorInput } from 'react-colorful';
import CheckBoxOutlineBlankIcon from '@mui/icons-material/CheckBoxOutlineBlank';
import CheckBoxIcon from '@mui/icons-material/CheckBox';
import React, { useState } from 'react';

const icon = <CheckBoxOutlineBlankIcon fontSize="small" />;
const checkedIcon = <CheckBoxIcon fontSize="small" />;

export default function EditOrderRoute({ auth, orderRoute, drivers, technicians }) {
    const [color, setColor] = useState(orderRoute.area_color);
    const [isEditMode, setisEditMode] = useState(false)

    // Reverse coordinates from lng lat (backend) to lat lng and format them
    const reverseCoordinates = (polygon) => {
        return polygon.coordinates[0].map(coordinate => ({
            lat: coordinate[1],
            lng: coordinate[0]
        }));
    };

    const reversedCoordinates = reverseCoordinates(orderRoute.area);
    
    const driversList = drivers.map((driver) => ({
        value: driver.user_id,
        label: `#${driver.user_id} - ${driver.name}`
    }));

    const techniciansList = technicians.map((technician) => ({
        value: technician.id,
        label: `#${technician.id} - ${technician.name}`
    }));

    const [selectedDrivers, setSelectedDrivers] = useState(
        orderRoute.drivers.map(driverId => 
            driversList.find(driver => driver.value === driverId.user_id)
        )
    );
    
    const [selectedTechnicians, setSelectedTechnicians] = useState(
        orderRoute.technicians.map(technicianId => 
            techniciansList.find(tech => tech.value === technicianId.id)
        )
    );

    const { data, setData, put, processing, errors } = useForm({
        id: orderRoute.id,
        name: orderRoute.name,
        area_coordinates: reversedCoordinates,
        area_color: orderRoute.area_color,
        usual_drivers: orderRoute.drivers.map(driver => driver.user_id),
        usual_technicians: orderRoute.technicians.map(tech => tech.id),
    });

    const toggleEdit = () => {
        setisEditMode(!isEditMode)
    }

    const onAreaChange = (area) => {
        const formattedArea = area[0].map(coord => ({ lat: coord.lat, lng: coord.lng })); // Convert to {lat, lng}
        setData('area_coordinates', formattedArea); // Update form data with the new polygon coordinates
    };

    const handleColorChange = (newValue) => {
        setColor(newValue);
        setData('area_color', newValue);
    };

    const handleDriversChange = (event, newValue) => {
        setSelectedDrivers(newValue);
        setData('usual_drivers', newValue.map(driver => driver.value)); // Update form data with selected driver IDs
    };

    const handleTechniciansChange = (event, newValue) => {
        setSelectedTechnicians(newValue);
        setData('usual_technicians', newValue.map(tech => tech.value)); // Update form data with selected technician IDs
    };

    console.log('data', data);

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('orderRoutes.edit', orderRoute.id)); // Send form data to the backend
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Editar Rota #{orderRoute.id}</h2>}
        >

            {<Head title='Editar Rota' />}

            <div className="py-12">
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">

                            <form className='mt-4' onSubmit={handleSubmit}>

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

                                <OrderRoutePolygon onAreaChange={onAreaChange} color={color} initialCoordinates={data.area_coordinates}/>

                                <TextField 
                                    label="Nome da Rota"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={!!errors.name}
                                    helperText={errors.name}
                                    disabled={!isEditMode}
                                    fullWidth
                                    required
                                    margin="normal"
                                />

                                <HexColorInput color={color} onChange={handleColorChange} placeholder="Cor da Rota" className='mt-4 mb-1 border-gray-300 rounded-lg'/>
                                <HexColorPicker color={color} onChange={handleColorChange} className='mb-3'/>
                                <br />

                                {/* Autocomplete for Drivers (Multiple Selection) */}
                                <Grid item xs={12} margin="normal">
                                    <Autocomplete
                                        multiple
                                        id="drivers"
                                        options={driversList}
                                        disableCloseOnSelect
                                        value={selectedDrivers}
                                        onChange={handleDriversChange}
                                        getOptionLabel={(option) => option.label}
                                        disabled={!isEditMode}
                                        isOptionEqualToValue={(option, value) => option.value === value.value}
                                        renderOption={(props, option, { selected }) => (
                                            <li {...props}>
                                                <Checkbox
                                                    icon={icon}
                                                    checkedIcon={checkedIcon}
                                                    style={{ marginRight: 8 }}
                                                    checked={selected}
                                                />
                                                {option.label}
                                            </li>
                                        )}
                                        renderInput={(params) => (
                                            <TextField
                                                {...params}
                                                label="Condutores"
                                                error={!!errors.usual_drivers}
                                                helperText={errors.usual_drivers}
                                                fullWidth
                                            />
                                        )}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>

                                {/* Autocomplete for Technicians (Multiple Selection) */}
                                <Grid item xs={12} margin="normal">
                                    <Autocomplete
                                        multiple
                                        id="technicians"
                                        options={techniciansList}
                                        disableCloseOnSelect
                                        value={selectedTechnicians}
                                        onChange={handleTechniciansChange}
                                        getOptionLabel={(option) => option.label}
                                        disabled={!isEditMode}
                                        isOptionEqualToValue={(option, value) => option.value === value.value}
                                        renderOption={(props, option, { selected }) => (
                                            <li {...props}>
                                                <Checkbox
                                                    icon={icon}
                                                    checkedIcon={checkedIcon}
                                                    style={{ marginRight: 8 }}
                                                    checked={selected}
                                                />
                                                {option.label}
                                            </li>
                                        )}
                                        renderInput={(params) => (
                                            <TextField
                                                {...params}
                                                label="Técnicos"
                                                error={!!errors.usual_technicians}
                                                helperText={errors.usual_technicians}
                                                fullWidth
                                            />
                                        )}
                                        sx={{ mb: 2 }}
                                    />
                                </Grid>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}