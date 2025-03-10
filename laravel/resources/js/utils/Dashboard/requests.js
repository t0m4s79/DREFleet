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

export const parseTopRequestVehicles = (requests) => {
    const vehicleData = requests.reduce((acc, request) => {
        const { vehicle_id, total_cost, items_cost } = request;
        if (!acc[vehicle_id]) {
            acc[vehicle_id] = { license_plate: request.vehicle.license_plate, total_requests: 0, total_value: 0, items_cost: request.items_cost };
        }
        acc[vehicle_id].total_requests += 1;

        let total = 0;

        // If report has items cost (Maintenance Report)
        if (items_cost) {
            total = Object.values(items_cost)
                .map(value => {
                    if (typeof value === "string") {
                        return parseFloat(value.replace(',', '.')) || 0;
                    }
                    return typeof value === "number" ? value : 0;
                })
                .reduce((acc, curr) => acc + curr, 0);

        // If report doesn't have items cost but have a total cost
        } else if (total_cost) {
            total = parseFloat(total_cost) || 0;
        }

        acc[vehicle_id].total_value += total;
        return acc;
    }, {});

    const sortedByRequests = Object.values(vehicleData)
        .sort((a, b) => b.total_requests - a.total_requests)
        .slice(0, 3);

    const sortedByValue = Object.values(vehicleData)
        .sort((a, b) => b.total_value - a.total_value)
        .slice(0, 3);

    const uniqueVehicles = Array.from(new Set([...sortedByRequests, ...sortedByValue].map(v => v.license_plate)))
        .map(plate => vehicleData[Object.keys(vehicleData).find(id => vehicleData[id].license_plate === plate)]);

    return uniqueVehicles;
}