const filterInServiceTechnicians = (technicians) => {
    return technicians.filter(technicians => technicians.status === 'Em Serviço');
};


const filterAvailableTechnicians = (technicians) => {
    return technicians.filter(technicians => technicians.status === 'Disponível');
};

export const parseTechnicians = (technicians) => {

    return {
        'inServiceTechnicians': filterInServiceTechnicians(technicians),
        'availableTechnicians': filterAvailableTechnicians(technicians)
    }
}