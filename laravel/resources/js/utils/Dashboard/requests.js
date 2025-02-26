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

    return chartData;
};
