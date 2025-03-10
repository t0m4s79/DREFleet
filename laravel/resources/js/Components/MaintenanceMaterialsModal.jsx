import React, { useState } from 'react';
import { Button, Modal, Typography, List, ListItem, ListItemText } from '@mui/material';

export default function MaintenanceMaterialsModal({ items }) {
    const [open, setOpen] = useState(false);
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    return (
        <div className="justify-center">
            <Button
                variant="outlined"
                onClick={handleOpen}
                sx={{
                    maxHeight: '30px',
                    minHeight: '30px',
                    margin: '0px 4px',
                }}
            >
                Ver
            </Button>
            <Modal
                open={open}
                onClose={handleClose}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
            >
                <div
                    style={{
                        backgroundColor: 'white',
                        padding: '20px',
                        borderRadius: '8px',
                        minWidth: '300px',
                    }}
                >
                    <Typography variant="h6" gutterBottom>
                        Materiais de Manutenção
                    </Typography>
                    <List>
                        {Object.entries(items).map(([key, value]) => (
                            <ListItem key={key} sx={{ padding: 0 }}>
                                <ListItemText primary={`${key.charAt(0).toUpperCase()}${key.slice(1)}: ${value} €`} />
                            </ListItem>
                        ))}
                    </List>
                    <Button onClick={handleClose} variant="contained" color="primary" fullWidth>
                        Fechar
                    </Button>
                </div>
            </Modal>
        </div>
    );
}
