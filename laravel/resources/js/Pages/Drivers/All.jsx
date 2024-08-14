import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function All( {auth, users, csrfToken} ) {

    const user = users.map((user)=>(
        <option key={user.id} value={user.id}>{user.id} - {user.name}</option>
    ));
    
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

            <h2>Criar condutor a partir de utilizador existente</h2>            
            <form action="/drivers/create" method='POST'>
                <input type="hidden" name="_token" value={csrfToken} />
                    <p>Selecione o utilizador</p>
                    <select name="user_id" id="">
                        {user}
                    </select>

                    <p>Carta de Pesados</p>
                    <input type="radio" name="heavy_license" value="0"/>
                    <label>Não</label><br/>
                    <input type="radio" name="heavy_license" value="1"/>
                    <label>Sim</label><br/>
                    <p><button type="submit" value="Submit">Submeter</button></p>
            </form>


        </AuthenticatedLayout>
    );
}