import React, { useState } from 'react'
import { Button, Modal } from '@mui/material';
import { useEffect } from 'react';
import LeafletMap from './LeafletMap';

export default function MapModal({ trajectory, route }) {

    const [open, setOpen] = useState(false);
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    let traject;
    let routeArea;
    let color;

    if (trajectory) {
        traject = JSON.parse(trajectory);
    }
    if (route) {
        routeArea = JSON.parse(route.area)
        color = route.color
    }

    return (
        <div className='justify-center'>
            <Button variant='outlined' onClick={handleOpen}>Consultar</Button>
            <Modal
                open={open}
                onClose={handleClose}
                style={{ display:'flex', alignItems:'center', justifyContent:'center' }}
            >
                
                <div style={{ top: '50%', margin: 'auto', width: '80%', height: '80%', backgroundColor: 'white', padding: '20px', borderRadius: "8px" }}>
                    <LeafletMap routing={false} onTrajectoryChange={trajectory} polygonCoordinates={routeArea} polygonColor={color} trajectory={traject}/>
                </div>

            </Modal>
        </div>
    )
}
