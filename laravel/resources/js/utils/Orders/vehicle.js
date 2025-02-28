import { differenceInDays, parse } from "date-fns";

const documentsExpirations = (documents) => {
    const today = new Date();

    const expiredDocuments = documents.filter(doc => {
        const expirationDate = parse(doc.expiration_date, 'dd-MM-yyyy', new Date());
        return differenceInDays(expirationDate, today) < 0;
    }).length;

    const expiringDocuments = documents.filter(doc => {
        const expirationDate = parse(doc.expiration_date, 'dd-MM-yyyy', new Date());
        const daysToExpire = differenceInDays(expirationDate, today);
        return daysToExpire < 30 && daysToExpire > 0;
    }).length;

    return { expiredDocuments, expiringDocuments }
}

const accessoriesExpirations = (accessories) => {
    const today = new Date();

    const expiredAccessories = accessories.filter(acc => {
        const expirationDate = parse(acc.expiration_date, 'dd-MM-yyyy', new Date());
        return differenceInDays(expirationDate, today) < 0;
    }).length;

    const expiringAccessories = accessories.filter(acc => {
        const expirationDate = parse(acc.expiration_date, 'dd-MM-yyyy', new Date());
        const daysToExpire = differenceInDays(expirationDate, today);
        return daysToExpire < 30 && daysToExpire > 0;
    }).length;

    return { expiredAccessories, expiringAccessories }
}

export const parseVehicleExpirations = (documents, accessories) => {
    const { expiredDocuments, expiringDocuments } = documentsExpirations(documents)
    const { expiredAccessories, expiringAccessories } = accessoriesExpirations(accessories)

    return {
        expired: expiredDocuments + expiredAccessories,
        expiring: expiringDocuments + expiringAccessories
    }
}