import { Box, Divider, List, ListItem, ListItemText, Typography } from "@mui/material";

export default function CustomBarChartTooltip({ x, y, data }) {    
    
    if (!Array.isArray(data)) {
        return
    }

    const [license_plate, total_requests, total_value] = data;

    return (
        <Box
            sx={{
                position: "absolute",
                left: x,
                top: y,
                backgroundColor: "white",
                padding: "6px 10px",
                borderRadius: 1,
                boxShadow: 3,
                minWidth: 200,
                zIndex: 10,
                border: "1px solid #E0E0E0",
                fontFamily: '"Roboto", sans-serif',
            }}
        >
            <Typography variant="body2" fontWeight="bold" color="primary" gutterBottom sx={{ marginBottom: 0.5 }}>
                Veículo: {license_plate[1]}
            </Typography>

            <Divider sx={{ marginBottom: 0.5 }} />

            <List sx={{ padding: 0, margin: 0 }}>
                <ListItem sx={{ paddingLeft: 0, paddingTop: 0, paddingBottom: 0 }}>
                    <ListItemText
                        primary={
                            <>
                                <Typography component="span" fontSize="0.9rem" color="text.secondary">
                                    {"Registos de Manutenção: "}
                                </Typography>
                                <Typography component="span" fontWeight={600} fontSize="0.9rem">
                                    {total_requests[1]}
                                </Typography>
                            </>
                        }
                    />
                </ListItem>
                <ListItem sx={{ paddingLeft: 0, paddingTop: 0, paddingBottom: 0 }}>
                    <ListItemText
                        primary={
                            <>
                                <Typography component="span" fontSize="0.9rem" color="text.secondary">
                                    {"Valor Gasto: "}
                                </Typography>
                                <Typography component="span" fontWeight={600} fontSize="0.9rem">
                                    {parseFloat(total_value[1]).toFixed(2)} €
                                </Typography>
                            </>
                        }
                    />
                </ListItem>
            </List>
        </Box>
    );
};
