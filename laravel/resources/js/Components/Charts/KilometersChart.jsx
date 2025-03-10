import React from "react";
import { BarChart } from "@mui/x-charts/BarChart";
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
                Veículos com Mais Quilómetros Percorridos
            </Typography>

            <BarChart
                dataset={data}
                xAxis={[{ scaleType: "linear", label: "Km" }]}
                yAxis={[{ scaleType: "band", dataKey: "name", tickLabelSpacing: 10 }]}
                series={[{ dataKey: "km" }]}
                layout="horizontal"
                width={500}
                height={300}
            />
        </Box>
    );
};

export default KilometerBarChart;
