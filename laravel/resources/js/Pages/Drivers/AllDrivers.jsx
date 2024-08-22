import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';


export default function AllDrivers( {auth, drivers} ) {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // const user = users.map((user)=>(
    //     <option key={user.id} value={user.id}>{user.id} - {user.name}</option>
    // ));

    const driver = drivers.map((driver)=>(
        <div>
            <p key={driver.id}>{driver.name}</p>
        </div>
    ));

    console.log(drivers)
    
    //Deconstruct data to send to table component
    let cols;
    let driverInfo = drivers.map((driver) => (
        {id: driver.user_id, name: driver.name, email: driver.email, phone: driver.phone, heavy_license: driver.heavy_license , status: driver.status }
    ))

    if(drivers.length > 0){
        cols = Object.keys(driverInfo[0])
    }

    const driverColumnLabels = {
        id: 'ID',
        name: 'Nome',
        email: 'Email',
        phone: 'Numero de Telefone',
        heavy_license: 'Carta de pesados',
        status: 'Estado'
    };
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutores</h2>}
        >

            <Head title="Condutores" />
        

            <div className='m-2 p-6'>

                <a href={route('drivers.create')} className="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                    Novo Condutor
                </a>

                {drivers && cols && <Table data={driverInfo} columns={cols} editAction="drivers.showEdit" deleteAction="drivers.delete" dataId="user_id"/> }

            </div>

        </AuthenticatedLayout>
    );
}