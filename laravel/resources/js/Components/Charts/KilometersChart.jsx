import React from "react";
import { BarChart } from "@mui/x-charts/BarChart";
import Tooltip from "@mui/material/Tooltip";
import { Box, Typography } from "@mui/material";

const KilometerBarChart = ({ kilometersReports }) => {

    const vehicleData = kilometersReports.reduce((acc, report) => {
        const vehicle = report.vehicle;
        const distance = report.end_kilometrage - report.begin_kilometrage;
        const key = vehicle.license_plate;

        if (!acc[key]) {
            acc[key] = { name: key, km: 0, make: vehicle.make, model: vehicle.model };
        }
        acc[key].km += distance;

        return acc;
    }, {});

    const data = Object.values(vehicleData).sort((a, b) => b.km - a.km);

    return (
        <Box>
            <Typography variant="h6" align="center" gutterBottom>
                Veículos com Mais Quilômetros Percorridos
            </Typography>

            <BarChart
                dataset={data}
                xAxis={[{ scaleType: "linear" }]}
                yAxis={[{ scaleType: "band", dataKey: "name", tickLabelSpacing: 10 }]}
                series={[
                    {
                        dataKey: "km",
                        renderTooltip: (params) => (
                            <Tooltip title={`Matrícula: ${params.data.name} - ${params.data.km} km`}>
                                <span>{params.data.km} km</span>
                            </Tooltip>
                        ),
                    },
                ]}
                layout="horizontal"
                width={500}
                height={300}
            />
        </Box>
    );
};

export default KilometerBarChart;
