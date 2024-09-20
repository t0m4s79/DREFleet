import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Accordion, AccordionDetails, AccordionSummary, Button } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

export default function Dashboard({ auth, drivers=[], technicians=[], vehicles=[] }) {
    
    console.log('drivers', drivers);
    console.log('technicians', technicians);
    console.log('vehicles', vehicles);

    // const availableDrivers = drivers.map((driver)=> {   
    //     if(driver.status == "Disponível") {
    //         return driver
    //     } else {
    //         return 0;
    //     }
    // })

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">Bem vindo</div>

                        
                    </div>
                </div>
                
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2 className='pb-3 text-lg font-bold'>Condutores</h2>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                    expandIcon={<ExpandMoreIcon />}
                                    aria-controls="panel1-content"
                                    id="panel1-header"
                                >
                                    Condutores em Serviço
                                </AccordionSummary>
                                <AccordionDetails>
                                    {drivers.filter(driver => driver.status === 'Em Serviço').length > 0 ? (
                                        drivers.filter(driver => driver.status === 'Em Serviço').map(driver => (
                                            <div>
                                                <a key={`driver-${driver.id}`} href={route('drivers.edit', driver)}>
                                                    #{driver.id} - {driver.name}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum condutor em serviço.</div>
                                    )}
                                </AccordionDetails>   

                            </Accordion>
                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Condutores Disponíveis
                                </AccordionSummary>
                                <AccordionDetails>
                                    {drivers.filter(driver => driver.status === 'Disponível').length > 0 ? (
                                        drivers.filter(driver => driver.status === 'Disponível').map(driver => (
                                            <div>
                                                <a key={`driver-${driver.id}`} href={route('drivers.edit', driver)}>
                                                    #{driver.id} - {driver.name}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum condutor disponível.</div>
                                    )}
                                </AccordionDetails>   
                            </Accordion>
                        </div>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2 className='pb-3 text-lg font-bold'>Técnicos</h2>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                    expandIcon={<ExpandMoreIcon />}
                                    aria-controls="panel1-content"
                                    id="panel1-header"
                                >
                                Técnicos em Serviço
                                </AccordionSummary>

                                <AccordionDetails>
                                    {technicians.filter(technician => technician.status === 'Em Serviço').length > 0 ? (
                                        technicians.filter(technician => technician.status === 'Em Serviço').map(technician => (
                                            <div>
                                                <a key={`technician-${technician.id}`} href={route('technicians.edit', technician)}>
                                                    #{technician.id} - {technician.name}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum técnico em serviço.</div>
                                    )}
                                </AccordionDetails>   
                            </Accordion>
                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Técnicos Disponíveis
                                </AccordionSummary>
                                <AccordionDetails>
                                    {technicians.filter(technician => technician.status === 'Disponível').length > 0 ? (
                                        technicians.filter(technician => technician.status === 'Disponível').map(technician => (
                                            <div>
                                                <a key={`technician-${technician.id}`} href={route('technicians.edit', technician)}>
                                                    #{technician.id} - {technician.name}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum técnico disponível.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>
                        </div>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2 className='pb-3 text-lg font-bold'>Veículos</h2>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                    expandIcon={<ExpandMoreIcon />}
                                    aria-controls="panel1-content"
                                    id="panel1-header"
                                >
                                Veículos em Serviço
                                </AccordionSummary>
                                <AccordionDetails>
                                    {vehicles.filter(vehicle => vehicle.status === 'Em Serviço').length > 0 ? (
                                        vehicles.filter(vehicle => vehicle.status === 'Em Serviço').map(vehicle => (
                                            <div>
                                                <a key={`vehicle-${vehicle.id}`} href={route('vehicles.edit', vehicle)}>
                                                    #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum veículo em serviço.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>
                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Veículos Disponíveis
                                </AccordionSummary>
                                <AccordionDetails>
                                    {vehicles.filter(vehicle => vehicle.status === 'Disponível').length > 0 ? (
                                        vehicles.filter(vehicle => vehicle.status === 'Disponível').map(vehicle => (
                                            <div>
                                                <a key={`vehicle-${vehicle.id}`} href={route('vehicles.edit', vehicle)}>
                                                    #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum veículo disponível.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Veículos em Manutenção
                                </AccordionSummary>
                                <AccordionDetails>
                                    {vehicles.filter(vehicle => vehicle.status === 'Em Manutenção').length > 0 ? (
                                        vehicles.filter(vehicle => vehicle.status === 'Em Manutenção').map(vehicle => (
                                            <div>
                                                <a key={`vehicle-${vehicle.id}`} href={route('vehicles.edit', vehicle)}>
                                                    #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum veículo em Manutenção.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>
                        </div>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2 className='pb-3 text-lg font-bold'>Pedidos</h2>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel1-content"
                                id="panel1-header"
                                >
                                Pedidos em Curso
                                </AccordionSummary>
                                <AccordionDetails>
                                <ul className="px-4" style={{listStyleType: 'disc'}}>
                                    <li>Coffee</li>
                                    <li>Tea</li>
                                    <li>Milk</li>
                                </ul>
                                </AccordionDetails>
                            </Accordion>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Pedidos Agendados
                                </AccordionSummary>
                                <AccordionDetails>
                                <ul className="px-4" style={{listStyleType: 'disc'}}>
                                    <li>Coffee</li>
                                    <li>Tea</li>
                                    <li>Milk</li>
                                </ul>
                                </AccordionDetails>
                            </Accordion>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Pedidos por Aprovar
                                </AccordionSummary>
                                <AccordionDetails>
                                <ul className="px-4" style={{listStyleType: 'disc'}}>
                                    <li>Coffee</li>
                                    <li>Tea</li>
                                    <li>Milk</li>
                                </ul>
                                </AccordionDetails>
                            </Accordion>
                        </div>
                    </div>
                </div>


            </div>
        </AuthenticatedLayout>
    );
}
