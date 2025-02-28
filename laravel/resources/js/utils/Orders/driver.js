import { differenceInDays, parse } from "date-fns";

const licenseExpirations = (driver) => {
    const today = new Date();

    const expirationDate = parse(driver.license_expiration_date, 'yyyy-MM-dd', new Date());
    const daysToExpire = differenceInDays(expirationDate, today);

    let expiredLicense = 0;
    let expiringLicense = 0;

    if (daysToExpire < 0) {
        expiredLicense = 1;
    } else if (daysToExpire < 30) {
        expiringLicense = 1;
    }

    return { expiredLicense, expiringLicense }
}

const tccExpirations = (driver) => {
    const today = new Date();
    let expiredTcc = 0;
    let expiringTcc = 0;

    if (!driver.tcc_expiration_date) return { expiredTcc, expiringTcc };
    const expirationDate = parse(driver.tcc_expiration_date, 'yyyy-MM-dd', new Date());
    const daysToExpire = differenceInDays(expirationDate, today);

    if (daysToExpire < 0) {
        expiredTcc = 1;
    } else if (daysToExpire < 30) {
        expiringTcc = 1;
    }

    return { expiredTcc, expiringTcc }
}

export const parseDriverExpirations = (driver) => {
    const { expiredLicense, expiringLicense } = licenseExpirations(driver)
    const { expiredTcc, expiringTcc } = tccExpirations(driver)

    return {
        expired: expiredLicense + expiredTcc,
        expiring: expiringLicense + expiringTcc
    }
}