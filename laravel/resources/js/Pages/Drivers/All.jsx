import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function All( {auth } ) {

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>}
        >

            <Head title="Condutores" />

            <li>
                <ul>
                    Condutor 1
                </ul>
                <ul>
                    Condutor 2
                </ul>
                <ul>
                    Condutor 3
                </ul>
            </li>

            <div className='container w-full md-8 xl-12 mx-auto p-4 rounded-xl bg-slate-200'>

                <table className='w-full border-collapse text-center'>
                    <thead className=''>
                        <tr className='border border-b-slate-500'>
                            <th>Condutor</th>
                            <th >Telefone</th>
                            <th >Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr className='border border-b-slate-300'>
                            <td >Condutor 1</td>
                            <td >+351111111111</td>
                            <td> condutor1@email.com</td>
                        </tr>
                        <tr className='border border-b-slate-300'>
                            <td >Condutor 2</td>
                            <td >+351222222222</td>
                            <td> condutor2@email.com</td>
                        </tr>
                        <tr className='border border-b-slate-300'>
                            <td >Condutor 3</td>
                            <td >+351333333333</td>
                            <td> condutor3@email.com</td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </AuthenticatedLayout>
    );
}