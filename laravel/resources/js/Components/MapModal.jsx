import React, { useState } from 'react'
import { Button, Modal } from '@mui/material';
import { useEffect } from 'react';
import LeafletMap from './LeafletMap';

export default function MapModal({ trajectory }) {

    const [open, setOpen] = useState(false);
    //const [selectedRoute, setSelectedRoute] = useState([])
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    let traject;
    let routeArea;
    let color;
    // useEffect(()=>{
    if (trajectory) {
        traject = JSON.parse(trajectory);
    }
    if (route) {
        routeArea = JSON.parse(route.area)
        color = route.color
    }
    //     console.log(traject)
    //     setSelectedRoute(traject)
    // }, trajectory)

    console.log('traject', traject)

    return (
        <div className='justify-center'>
            <Button onClick={handleOpen}>Ver Rota</Button>
            <Modal
                open={open}
                onClose={handleClose}
                style={{ display:'flex', alignItems:'center', justifyContent:'center' }}
            >
                <div style={{ top: '50%', margin: 'auto', width: '80%', height: '80%', backgroundColor: 'white', padding: '20px' }}>
                    <LeafletMap routing={false} onTrajectoryChange={trajectory} polygonCoordinates={routeArea} polygonColor={color}/>
                </div>

            </Modal>
        </div>
    )
}
