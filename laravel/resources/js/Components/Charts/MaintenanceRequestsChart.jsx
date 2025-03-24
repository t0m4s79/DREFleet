import * as React from 'react';
import { Box, Typography } from "@mui/material";
import { LineChart } from "@mui/x-charts";
import { styled } from "@mui/material/styles";
import { parseMaintenanceRequests } from '@/utils/Dashboard/requests';
import CustomLineChartTooltip from './CustomLineChartToolTip';

const ChartContainer = styled(Box)(({ theme }) => ({
    backgroundColor: '#F8F8F8',
    borderRadius: theme.shape.borderRadius,
    boxShadow: theme.shadows[2],
    maxWidth: '100%',
    margin: 'auto',
}));

const Title = styled(Typography)(({ theme }) => ({
    color: theme.palette.text.primary,
    fontWeight: 600,
    marginBottom: theme.spacing(2),
}));


const MaintenanceRequestsChart = ({ maintenanceRequests }) => {
    const maintenanceRequestsData = parseMaintenanceRequests(maintenanceRequests);

    const axisContent = (tooltipProps) => {
        const { x, y } = tooltipProps;

        const { dataIndex } = tooltipProps;

        if (!maintenanceRequestsData[dataIndex]) {
            return 
        }

        const { vehicles } = maintenanceRequestsData[dataIndex];
        const data = Object.entries(vehicles);

        return <CustomLineChartTooltip x={x} y={y} data={data} />;
    };

    return (
        <ChartContainer className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
            <Title variant="h6" align="center">
                Registos de Manutenção
            </Title>

            <LineChart
                xAxis={[{
                    scaleType: 'point',
                    data: maintenanceRequestsData.map(d => d.month),
                }]}
            series={[{
                data: maintenanceRequestsData.map(d => d.totalRequests),
                area: true,
                showMark: false,
                color: "#0EA5E9",
                valueFormatter: (value, { dataIndex }) => {
                    const { vehicles } = maintenanceRequestsData[dataIndex];
                    return Object.entries(vehicles)
                        .map(([vehicle, count]) => `${count} × ${vehicle}`)
                        .join("\n") || "Sem registos";
                },
            }]}
            width={600}
            height={300}
            tooltip={{
                trigger: 'axis'
            }}
            slots={{
                axisContent
            }}
            />
        </ChartContainer>

    );
};

export default MaintenanceRequestsChart;
