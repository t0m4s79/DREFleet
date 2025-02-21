import { parse } from "date-fns";

const parseDate = (dateString) => parse(dateString, "dd-MM-yyyy HH:mm", new Date());

const filterOngoingOrders = (orders) => {
    const now = new Date();
    return orders.filter(order => {
        const startDate = parseDate(order.expected_begin_date);
        const endDate = parseDate(order.expected_end_date);
        return endDate >= now && (order.status === "Em curso" || order.status === "Interrompido");
    });
};

const filterApprovedOrders = (orders) => {
    const now = new Date();
    return orders.filter(order => {
        const startDate = parseDate(order.expected_begin_date);
        return startDate > now && order.status === "Aprovado";
    });
};

const filterOrdersToApprove = (orders) => {
    return orders.filter(order => order.status === "Por aprovar")
}

export const parseOrders = (orders) => {

    return {
        'ongoingOrders': filterOngoingOrders(orders),
        'approvedOrders': filterApprovedOrders(orders),
        'ordersToAprove': filterOrdersToApprove(orders)
    }
}