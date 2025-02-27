import { Box, Typography } from "@mui/material";
import { parseRequests } from "@/utils/Dashboard/requests";
import { LineChart } from "@mui/x-charts";

const RefuelRequestsChart = ({ refuelRequests }) => {
    const refuelRequestsData = parseRequests(refuelRequests);

    return (
        <Box>
            <Typography variant="h6" align="center" gutterBottom>
                Registos de Abastecimento
            </Typography>


            <LineChart
                xAxis={[{ scaleType: 'point', data: refuelRequestsData.map(d => d.month) }]}
                series={[{ data: refuelRequestsData.map(d => d.requests) }]}
                width={600}
                height={300}
            />
        </Box>
    );
};

export default RefuelRequestsChart;
