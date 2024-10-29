import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Accordion, AccordionDetails, AccordionSummary, Button } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

export default function Dashboard({ auth, drivers=[], technicians=[], vehicles=[], orders=[] }) {
    
    // const availableDrivers = drivers.map((driver)=> {   
    //     if(driver.status == "Disponível") {
    //         return driver
    //     } else {
    //         return 0;
    //     }
    // })

    console.log(orders.filter(order => new Date(order.expected_begin_date) > new Date()))
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Painel de Controlo</h2>}
        >
            <Head title="Painel de Controlo" />

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
                                    id="panel1-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                    Condutores em Serviço
                                </AccordionSummary>
                                <AccordionDetails>
                                    {drivers.filter(driver => driver.status === 'Em Serviço').length > 0 ? (
                                        drivers.filter(driver => driver.status === 'Em Serviço').map(driver => (
                                            <div>
                                                <a key={`driver-${driver.id}`} href={route('drivers.edit', driver)}>
                                                    #{driver.id} - {driver.name} - {driver.driver.license_number}
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
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                Condutores Disponíveis
                                </AccordionSummary>
                                <AccordionDetails>
                                    {drivers.filter(driver => driver.status === 'Disponível').length > 0 ? (
                                        drivers.filter(driver => driver.status === 'Disponível').map(driver => (
                                            <div>
                                                <a key={`driver-${driver.id}`} href={route('drivers.edit', driver)}>
                                                    #{driver.id} - {driver.name} - {driver.driver.license_number}
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
                                    id="panel1-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
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
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
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
                                    id="panel1-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
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
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
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
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                Veículos em Manutenção
                                </AccordionSummary>
                                <AccordionDetails>
                                    {vehicles.filter(vehicle => vehicle.status === 'Em manutenção').length > 0 ? (
                                        vehicles.filter(vehicle => vehicle.status === 'Em manutenção').map(vehicle => (
                                            <div>
                                                <a key={`vehicle-${vehicle.id}`} href={route('vehicles.edit', vehicle)}>
                                                    #{vehicle.id} - {vehicle.make} {vehicle.model} - {vehicle.license_plate}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum veículo em manutenção.</div>
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
                                id="panel1-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                Pedidos em Curso
                                </AccordionSummary>
                                <AccordionDetails>
                                    {orders.filter(order => new Date(order.expected_begin_date) <= new Date() && new Date(order.expected_end_date) >= new Date()).length > 0 ? (
                                        orders.filter(order => new Date(order.expected_begin_date) <= new Date() && new Date(order.expected_end_date) >= new Date() || order.status === 'Em curso').map(order => (
                                            <div key={`order-${order.id}`}>
                                                <a href={route('orders.edit', order)}>
                                                    #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum pedido em curso.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                Pedidos Agendados
                                </AccordionSummary>
                                <AccordionDetails>
                                    {orders.filter(order => new Date(order.expected_begin_date) > new Date() && order.status === 'Aprovado').length > 0 ? (
                                        orders.filter(order => new Date(order.expected_begin_date) > new Date() && order.status === 'Aprovado').map(order => (
                                            <div key={`order-${order.id}`}>
                                                <a href={route('orders.edit', order)}>
                                                    #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum pedido agendado.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>

                            <Accordion style={{boxShadow: 'none'}}>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header" className='hover:transition hover:text-gray-400 aria-expanded:text-sky-400 aria-expanded:font-bold'
                                >
                                Pedidos por Aprovar
                                </AccordionSummary>
                                <AccordionDetails>
                                    {orders.filter(order => order.status === 'Por aprovar').length > 0 ? (
                                        orders.filter(order => order.status === 'Por aprovar').map(order => (
                                            <div>
                                                <a key={`order-${order.id}`} href={route('orders.edit', order)}>
                                                    #{order.id} - {order.order_type} - {order.expected_begin_date} a {order.expected_end_date}
                                                </a>
                                            </div>
                                        ))
                                    ) : (
                                        <div>Nenhum pedido por aprovar.</div>
                                    )}
                                </AccordionDetails>
                            </Accordion>
                        </div>
                    </div>
                </div>


            </div>
        </AuthenticatedLayout>
    );
}
