import { differenceInDays, isValid, parse } from "date-fns";

const filterInServiceDrivers = (drivers) => {
    return drivers.filter(driver => driver.status === 'Em Serviço');
};

const filterAvailableDrivers = (drivers) => {
    return drivers.filter(driver => driver.status === 'Disponível');
};

export const parseDrivers = (drivers) => {

    return {
        'inServiceDrivers': filterInServiceDrivers(drivers),
        'availableDrivers': filterAvailableDrivers(drivers)
    }
}

export const driversExpirations = (drivers) => {
    const today = new Date();
    const driverExpirations = new Map();

    drivers.forEach(driver => {
        const driverData = driver.driver;
        if (!driverExpirations.has(driverData.user_id)) {
            driverExpirations.set(driverData.user_id, {
                driver: driverData,
                expired: new Set(),
                expiring: new Set()
            });
        }

        const expirationData = driverExpirations.get(driverData.user_id);

        // License Verification
        if (driverData.license_expiration_date) {
            const licenseExpiration = parse(driverData.license_expiration_date, 'dd-MM-yyyy', new Date());
            if (isValid(licenseExpiration)) {
                const daysToExpire = differenceInDays(licenseExpiration, today);
                if (daysToExpire < 0) {
                    expirationData.expired.add("license");
                } else if (daysToExpire < 30) {
                    expirationData.expiring.add("license");
                }
            }
        }

        // TCC Verification
        if (driverData.tcc_expiration_date) {
            const tccExpiration = parse(driverData.tcc_expiration_date, 'dd-MM-yyyy', new Date());
            if (isValid(tccExpiration)) {
                const daysToExpire = differenceInDays(tccExpiration, today);
                if (daysToExpire < 0) {
                    expirationData.expired.add("tcc");
                } else if (daysToExpire < 30) {
                    expirationData.expiring.add("tcc");
                }
            }
        }
    });

    return Array.from(driverExpirations.values()).map(driver => ({
        ...driver,
        expired: Array.from(driver.expired),
        expiring: Array.from(driver.expiring)
    }));
};
