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