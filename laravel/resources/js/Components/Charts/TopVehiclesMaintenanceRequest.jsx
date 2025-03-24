import { Box, Divider, List, ListItem, ListItemText, Typography } from "@mui/material";
import { BarChart } from "@mui/x-charts";
import { styled } from "@mui/material/styles";
import { parseTopRequestVehicles } from "@/utils/Dashboard/requests";
import CustomBarChartTooltip from "./CustomBarChartTooltip";

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
    marginTop: theme.spacing(2),
}));

export default function TopVehiclesMaintenanceRequests({ maintenanceRequests }) {
    const uniqueVehicles = parseTopRequestVehicles(maintenanceRequests);

    const axisContent = (tooltipProps) => {
        const { x, y } = tooltipProps;

        const { dataIndex } = tooltipProps;
        const data = uniqueVehicles[dataIndex] ? Object.entries(uniqueVehicles[dataIndex]) : {};

        return <CustomBarChartTooltip x={x} y={y} data={data} />;
    };

    return (
        <ChartContainer className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
            <Title variant="h6" align="center">
                Veículos Manutenção - Registos e Valor Gasto
            </Title>

            <BarChart className="max-w-fit"
                xAxis={[{ scaleType: "band", data: uniqueVehicles.map(v => v.license_plate), label: "Veículos" }]}
                yAxis={[
                    { id: "leftAxis", scaleType: "linear", position: "left" },
                    { id: "rightAxis", scaleType: "linear", position: "right" }
                ]}
                series={[
                    { data: uniqueVehicles.map(v => v.total_requests), label: "Registos de Manutenção", color: "#0EA5E9", yAxisKey: "leftAxis" },
                    { data: uniqueVehicles.map(v => v.total_value), label: "Valor Gasto (€)", color: "#FF5722", yAxisKey: "rightAxis" }
                ]}
                layout="vertical"
                width={600}
                height={300}
                rightAxis="rightAxis"
                tooltip={{
                    trigger: 'axis'
                }}
                slots={{
                    axisContent
                }}
            />
        </ChartContainer>
    );
}