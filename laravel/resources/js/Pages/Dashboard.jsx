import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Accordion, AccordionDetails, AccordionSummary } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

export default function Dashboard({ auth }) {
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
                    <div className="pb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2>Condutores</h2>

                            {/* TODO: Add drivers according to their status */}
                            <Accordion>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel1-content"
                                id="panel1-header"
                                >
                                Condutores Ativos
                                </AccordionSummary>
                                <AccordionDetails>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
                                malesuada lacus ex, sit amet blandit leo lobortis eget.
                                </AccordionDetails>
                            </Accordion>
                            <Accordion>
                                <AccordionSummary
                                expandIcon={<ExpandMoreIcon />}
                                aria-controls="panel2-content"
                                id="panel2-header"
                                >
                                Condutores disponívies
                                </AccordionSummary>
                                <AccordionDetails>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
                                malesuada lacus ex, sit amet blandit leo lobortis eget.
                                </AccordionDetails>
                            </Accordion>
                        </div>
                    </div>


                </div>

                {/* TODO: Change divs to use Accordion and add related information */}
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="pb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2>Veículos</h2>
                        </div>
                        <div className='px-9'>
                            <h4>Veículos Ativos</h4>
                        </div>

                        <div className='px-9'>
                            <h4>Veículos Disponíveis</h4>
                        </div>
                    </div>

                </div>

                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="pb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-8 border-sky-600">
                        <div className="p-6 text-gray-900">
                            <h2>Viagens</h2>
                        </div>
                        <div className='px-9'>
                            <h4>Viagens em Curso</h4>
                        </div>

                        <div className='px-9'>
                            <h4>Viagens Agendadas</h4>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
