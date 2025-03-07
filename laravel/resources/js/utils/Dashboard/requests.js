import dayjs from "dayjs";

export const parseRequests = (requests) => {
    const groupedData = {};

    requests.forEach((request) => {
        const month = dayjs(request.date).format("MM/YY");
        const vehicleName = `${request.vehicle.license_plate}`;

        if (!groupedData[month]) {
            groupedData[month] = {};
        }

        if (!groupedData[month][vehicleName]) {
            groupedData[month][vehicleName] = 0;
        }

        groupedData[month][vehicleName] += 1;
    });

    let chartData = Object.keys(groupedData).map(month => ({
        month,
        vehicles: groupedData[month],
        totalRequests: Object.values(groupedData[month]).reduce((sum, count) => sum + count, 0),
    })).sort((a, b) => {
        const [monthA, yearA] = a.month.split("/").map(Number);
        const [monthB, yearB] = b.month.split("/").map(Number);
        return yearA !== yearB ? yearA - yearB : monthA - monthB;
    });

    if (chartData.length > 12) {
        chartData = chartData.slice(chartData.length - 12);
    }

    return chartData;
};

export const parseMaintenanceRequests = (requests) => {
    const groupedData = {};

    requests.forEach((request) => {
        const month = dayjs(request.begin_date).format("MM/YY");
        const vehicleName = `${request.vehicle.license_plate}`;

        if (!groupedData[month]) {
            groupedData[month] = {};
        }

        if (!groupedData[month][vehicleName]) {
            groupedData[month][vehicleName] = 0;
        }

        groupedData[month][vehicleName] += 1;
    });

    let chartData = Object.keys(groupedData).map(month => ({
        month,
        vehicles: groupedData[month],
        totalRequests: Object.values(groupedData[month]).reduce((sum, count) => sum + count, 0),
    })).sort((a, b) => {
        const [monthA, yearA] = a.month.split("/").map(Number);
        const [monthB, yearB] = b.month.split("/").map(Number);
        return yearA !== yearB ? yearA - yearB : monthA - monthB;
    });

    if (chartData.length > 12) {
        chartData = chartData.slice(chartData.length - 12);
    }

    return chartData;
};


export const parseRequestsByMonth = (requests, month) => {
    const filteredRequests = requests.filter((request) =>
        dayjs(request.date).format("MM/YY") === month
    );

    const groupedData = filteredRequests.reduce((acc, request) => {
        const vehicle = request.vehicle?.license_plate || "Desconhecido";
        acc[vehicle] = (acc[vehicle] || 0) + 1;
        return acc;
    }, {});

    return Object.entries(groupedData).map(([vehicle, requests]) => ({
        vehicle,
        requests,
    }));
};

export const parseMaintenanceRequestsByMonth = (requests, month) => {
    const filteredRequests = requests.filter((request) =>
        dayjs(request.begin_date).format("MM/YY") === month
    );

    const groupedData = filteredRequests.reduce((acc, request) => {
        const vehicle = request.vehicle?.license_plate || "Desconhecido";
        acc[vehicle] = (acc[vehicle] || 0) + 1;
        return acc;
    }, {});

    return Object.entries(groupedData).map(([vehicle, requests]) => ({
        vehicle,
        requests,
    }));
};
