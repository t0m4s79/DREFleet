import { parseMaintenanceRequests } from "@/utils/Dashboard/requests";
import { Box, Typography } from "@mui/material";
import { LineChart } from "@mui/x-charts";

const MaintenanceRequestsChart = ({ maintenanceRequests }) => {
    const maintenanceRequestsData = parseMaintenanceRequests(maintenanceRequests);

    return (
        <Box>
            <Typography variant="h6" align="center" gutterBottom>
                Registos de Manutenção
            </Typography>


            <LineChart
                xAxis={[{ scaleType: 'point', data: maintenanceRequestsData.map(d => d.month) }]}
                series={[{ data: maintenanceRequestsData.map(d => d.requests) }]}
                width={600}
                height={300}
            />
        </Box>
    );
};

export default MaintenanceRequestsChart;
