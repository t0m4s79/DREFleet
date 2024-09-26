import OrderRoutePolygon from '@/Components/OrderRoutePolygon'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm } from '@inertiajs/react';
import { Box, Button, TextField } from '@mui/material';
import { MuiColorInput } from 'mui-color-input';

import React, { useState } from 'react'

export default function NewOrderRoute({auth}) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Rota</h2>}
        >

            <div className='py-12'>
                
            </div>
        </AuthenticatedLayout>
    )
}