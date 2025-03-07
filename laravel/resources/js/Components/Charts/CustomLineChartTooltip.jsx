import { Box, Divider, List, ListItem, ListItemText, Typography } from "@mui/material";

export default function CustomLineChartTooltip ({ x, y, data }) {
    const totalRequests = data.reduce((total, [vehicle, count]) => total + count, 0);

    return (
        <Box
            sx={{
                position: "absolute",
                left: x,
                top: y,
                backgroundColor: "white",
                padding: '8px 12px',
                borderRadius: 1,
                boxShadow: 3,
                minWidth: 220,
                zIndex: 10,
                border: '1px solid #E0E0E0',
                fontFamily: '"Roboto", sans-serif',
            }}
        >
            {data ? (
                <>
                    <Typography variant="body2" fontWeight="bold" color="primary" gutterBottom sx={{ marginBottom: 0.5 }}>
                        Total de Registos: {totalRequests}
                    </Typography>

                    <Divider sx={{ marginBottom: 0.5 }} />

                    <List sx={{ padding: 0, margin: 0 }}>
                        {data.map(([vehicle, count]) => (
                            <ListItem key={vehicle} sx={{ paddingLeft: 0, paddingTop: 0, paddingBottom: 0 }}>
                                <ListItemText
                                    primary={
                                        <>
                                            <Typography component="span" fontWeight={600} fontSize="0.9rem">
                                                {count}
                                            </Typography>
                                            {' x '}
                                            <Typography component="span" fontSize="0.9rem" color="text.secondary">
                                                {vehicle}
                                            </Typography>
                                        </>
                                    }
                                />
                            </ListItem>
                        ))}
                    </List>
                </>
            ) : (
                <Typography variant="body2" color="text.secondary" sx={{ marginTop: 0 }}>
                    Sem dados
                </Typography>
            )}
        </Box>
    );
};