import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function AllVehicles( {auth, vehicles }) {

    console.log('vehicles', vehicles)
    let cols;

    if(vehicles.length > 0){
        cols = Object.keys(vehicles[0]);
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículos</h2>}
        >

            <Head title="Veículos" />

            {/* <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <table className='items-center bg-transparent w-full border-collapse '>
                                <tbody>
                                {vehicles.map( (vehicle) => (
                                    <tr key={vehicle.id}>
                                        {(Object.values(vehicle)).map((value,index)=>(
                                            <td key={index}>{value}</td>
                                        ))}
                                    </tr>
                                ))}
                                </tbody>                                
                            </table>
                        </div>
                    </div>
                </div>
                
            </div> */}

            {vehicles && cols && <Table data={vehicles} columns={cols}/>}

        </AuthenticatedLayout>
    )
}
