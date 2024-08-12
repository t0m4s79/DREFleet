import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

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
                        <div className="p-6 text-gray-900">You're logged in!</div>

                        
                    </div>
                </div>
                
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h2>Condutores</h2>
                        </div>
                        <div className='px-9'>
                            <h4>Condutores Ativos</h4>
                        </div>

                        <div className='px-9'>
                            <h4>Condutores Disponíveis</h4>
                        </div>
                    </div>


                </div>

                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
