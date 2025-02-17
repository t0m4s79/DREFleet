import { differenceInDays, isBefore, parse } from 'date-fns';

const documentsExpirations = (vehicle) => {
    const today = new Date();

    const expiredDocuments = vehicle.documents.filter(doc => {
        const expirationDate = parse(doc.expiration_date, 'yyyy-MM-dd', new Date());
        return differenceInDays(expirationDate, today) < 0;
    });

    const expiringDocuments = vehicle.documents.filter(doc => {
        const expirationDate = parse(doc.expiration_date, 'yyyy-MM-dd', new Date());
        return (differenceInDays(expirationDate, today) < 30 && differenceInDays(expirationDate, today) > 0);
    });

    return [expiredDocuments.length, expiringDocuments.length]
}

const acessoriesExpirations = (vehicle) => {
    const today = new Date();

    const expiredAccessories = vehicle.accessories.filter(acc => {
        if (acc.expiration_date) {
            const expirationDate = parse(acc.expiration_date, 'yyyy-MM-dd', new Date());
            return differenceInDays(expirationDate, today) < 0;
        }
    });

    const expiringAccessories = vehicle.accessories.filter(acc => {
        if (acc.expiration_date) {
            const expirationDate = parse(acc.expiration_date, 'yyyy-MM-dd', new Date());
            return (differenceInDays(expirationDate, today) < 30 && differenceInDays(expirationDate, today) > 0);
        }
    });

    return [expiredAccessories.length, expiringAccessories.length]
}

export const vehicleExpirations = (vehicle) => {
    const [expiredDocuments, expiringDocuments] = documentsExpirations(vehicle);
    const [expiredAccessories, expiringAccessories] = acessoriesExpirations(vehicle);
    const expired = expiredDocuments + expiredAccessories;
    const expiring = expiringDocuments + expiringAccessories;

    return [expired, expiring];
}

export const vehiclesExpirations = vehicles => {
    const calculateTotals = filteredVehicles => {
        let totalExpired = 0;
        let totalExpiring = 0;

        filteredVehicles.forEach(vehicle => {
            const [expired, expiring] = vehicleExpirations(vehicle);
            totalExpired += expired;
            totalExpiring += expiring;
        });

        return { totalExpired, totalExpiring };
    };

    return {
        inService: calculateTotals(vehicles.filter(vehicle => vehicle.status === 'Em Serviço')),
        available: calculateTotals(vehicles.filter(vehicle => vehicle.status === 'Disponível')),
        maintenance: calculateTotals(vehicles.filter(vehicle => vehicle.status === 'Em manutenção'))
    };
};