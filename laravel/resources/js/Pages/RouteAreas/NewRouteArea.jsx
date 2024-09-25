import RouteAreaPolygon from '@/Components/RouteAreaPolygon'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm } from '@inertiajs/react';
import { Box, Button, TextField } from '@mui/material';
import { MuiColorInput } from 'mui-color-input';

import React, { useState } from 'react'

export default function NewRouteArea({auth}) {
    //const [areaCoordinates, setAreaCoordinates] = useState([])
    const [color, setColor] = useState('#ffffff')

    const handleColorChange = (newValue) => {
      setColor(newValue)
    }

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        //color: color,
        area_coordinates: [], // Holds the polygon coordinates
    });

    console.log(data)

    const onAreaChange = (area) => {
        setData('area_coordinates', area[0]); // Update form data with the new polygon coordinates
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/routeAreas/create'); // Send form data to the backend
    };

    console.log('areaCoordinates', data.area_coordinates)

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Rota</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <RouteAreaPolygon onAreaChange={onAreaChange} color={color}/>

                            <form onSubmit={handleSubmit}>
                            <TextField
                                label="Nome da Rota"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                error={!!errors.name}
                                helperText={errors.name}
                                fullWidth
                                required
                                margin="normal"
                            />
                            <Box mt={2}>
                                <MuiColorInput format="hex" value={color} onChange={handleColorChange} isAlphaHidden/>
                            </Box>

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
    )
}
