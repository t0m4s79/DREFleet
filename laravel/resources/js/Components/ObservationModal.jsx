import React, { useState } from 'react'
import { Button, Modal, Typography } from '@mui/material';

export default function ObservationModal({ observations }) {

    const [open, setOpen] = useState(false);
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    return (
        <div className='justify-center'>
            <Button
                variant='outlined'
                onClick={handleOpen}
                sx={{
                    maxHeight: '30px',
                    minHeight: '30px',
                    margin: '0px 4px'
                }}
            >
                Ver
            </Button>
            <Modal
                open={open}
                onClose={handleClose}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
            >

                <div style={{ top: '50%', margin: 'auto', backgroundColor: 'white', padding: '20px', borderRadius: "8px" }}>
                    <Typography sx={{ whiteSpace: 'pre-line' }}>{observations}</Typography>
                </div>
            </Modal>
        </div>
    )
}
