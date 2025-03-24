import * as React from 'react';
import { Box, Typography } from "@mui/material";
import { parseRequests } from "@/utils/Dashboard/requests";
import { LineChart } from "@mui/x-charts";
import { styled } from "@mui/material/styles";
import CustomLineChartTooltip from './CustomLineChartToolTip';
import { Link } from '@inertiajs/react';

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

const StyledLink = styled(Link)(({ theme }) => ({
    display: 'flex',
    justifyContent: 'center',
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(1),
    color: theme.palette.primary.main,
    fontWeight: 600,
    fontSize: '1rem',
    textDecoration: 'none',
    transition: 'color 0.3s ease, transform 0.3s ease',

    '&:hover': {
        color: theme.palette.primary.dark,
        transform: 'scale(1.05)',
    },
}));

const RefuelRequestsChart = ({ refuelRequests }) => {
    const refuelRequestsData = parseRequests(refuelRequests);

    const axisContent = (tooltipProps) => {
        const { x, y } = tooltipProps;

        const { dataIndex } = tooltipProps;

        if (!refuelRequestsData[dataIndex]) {
            return 
        }

        const { vehicles } = refuelRequestsData[dataIndex];
        const data = Object.entries(vehicles);

        return <CustomLineChartTooltip x={x} y={y} data={data} />;
    };

    return (
        <ChartContainer className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
            <Title variant="h6" align="center">
                Registos de Abastecimento
            </Title>

            <LineChart
                xAxis={[{
                    scaleType: 'point',
                    data: refuelRequestsData.map(d => d.month),
                }]}
                series={[{
                    data: refuelRequestsData.map(d => d.totalRequests),
                    area: true,
                    showMark: false,
                    color: "#0EA5E9",
                    valueFormatter: (value, { dataIndex }) => {
                        const { vehicles } = refuelRequestsData[dataIndex];
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

            {/*
            <StyledLink href="/registos-abastecimento">
                Ver Registos de Abastecimento
            </StyledLink>
            */}
        </ChartContainer>
    );
};

export default RefuelRequestsChart;
