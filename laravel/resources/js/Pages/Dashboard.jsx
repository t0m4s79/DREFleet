import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Accordion, AccordionDetails, AccordionSummary } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

export default function Dashboard({ auth, drivers, tehcnicians, vehicles }) {
    console.log(drivers);
    console.log(tehcnicians);
    console.log(vehicles);
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
                                Condutores Disponíveis
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
                                Técnicos Disponíveis
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
                                Veículos Disponíveis
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
                                Veículos em Manutenção
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
                                Veículos Indisponíveis
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
                                Viagens em Curso
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
                                Viagens Agendadas
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
