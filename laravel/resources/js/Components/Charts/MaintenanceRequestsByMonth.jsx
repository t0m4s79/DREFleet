import { useState } from "react";
import { Box, Typography } from "@mui/material";
import { BarChart } from "@mui/x-charts";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import dayjs from "dayjs";
import { parseMaintenanceRequestsByMonth } from "@/utils/Dashboard/requests";

const MaintenanceRequestsByMonthChart = ({ maintenanceRequests }) => {
    const months = [...new Set(maintenanceRequests.map(req => dayjs(req.date).format("MM/YY")))]
        .sort((a, b) => {
            const [monthA, yearA] = a.split("/").map(Number);
            const [monthB, yearB] = b.split("/").map(Number);
            return yearA !== yearB ? yearA - yearB : monthA - monthB;
        });

    const [selectedMonth, setSelectedMonth] = useState(dayjs(months[0] || "10/24", "MM/YY"));

    const maintenanceRequestsByMonthData = parseMaintenanceRequestsByMonth(maintenanceRequests, selectedMonth.format("MM/YY"));

    return (
        <Box>
            <Typography variant="h6" align="center" gutterBottom>
                Registos de Manutenção por Veículo ({selectedMonth.format("MM/YY")})
            </Typography>

            <DatePicker
                views={["year", "month"]}
                label="Escolha o mês"
                value={selectedMonth}
                onChange={(date) => setSelectedMonth(date)}
                format="MM/YY"
                slotProps={{ textField: { variant: "outlined", sx: { mb: 2, width: 200 } } }}
            />

            <BarChart
                xAxis={[
                    {
                        scaleType: "band",
                        data: maintenanceRequestsByMonthData.map(d => d.vehicle),
                        tickLabelStyle: { fontSize: 12, angle: -45, textAnchor: "end" },
                    }
                ]}
                series={[{ data: maintenanceRequestsByMonthData.map(d => d.requests) }]}
                width={600}
                height={300}
            />
        </Box>
    );
};

export default MaintenanceRequestsByMonthChart;
