import dayjs from "dayjs";

export const parseRequests = (requests) => {
    const requestsCount = requests.map((request) => ({
        month: dayjs(request.date).format("MM/YY"),
        requests: 1,
    }));

    const groupedData = requestsCount.reduce((acc, { month, requests }) => {
        acc[month] = (acc[month] || 0) + requests;
        return acc;
    }, {});

    let chartData = Object.keys(groupedData)
        .map(month => ({
            month,
            requests: groupedData[month]
        }))
        .sort((a, b) => {
            const [monthA, yearA] = a.month.split("/").map(Number);
            const [monthB, yearB] = b.month.split("/").map(Number);
            return yearA !== yearB ? yearA - yearB : monthA - monthB;
        });

    // Return last 12 months
    if (chartData.length > 12) {
        chartData = chartData.slice(chartData.length - 12, chartData.length);
    }

    return chartData;
};

export const parseMaintenanceRequests = (requests) => {
    const requestsCount = requests.map((request) => ({
        month: dayjs(request.begin_date).format("MM/YY"),
        requests: 1,
    }));

    const groupedData = requestsCount.reduce((acc, { month, requests }) => {
        acc[month] = (acc[month] || 0) + requests;
        return acc;
    }, {});

    let chartData = Object.keys(groupedData)
        .map(month => ({
            month,
            requests: groupedData[month]
        }))
        .sort((a, b) => {
            const [monthA, yearA] = a.month.split("/").map(Number);
            const [monthB, yearB] = b.month.split("/").map(Number);
            return yearA !== yearB ? yearA - yearB : monthA - monthB;
        });

    // Return last 12 months
    if (chartData.length > 12) {
        chartData = chartData.slice(chartData.length - 12, chartData.length);
    }

    return chartData;
}

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
