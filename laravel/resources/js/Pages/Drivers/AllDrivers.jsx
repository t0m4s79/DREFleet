import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';


export default function AllDrivers( {auth, users, drivers} ) {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const user = users.map((user)=>(
        <option key={user.id} value={user.id}>{user.id} - {user.name}</option>
    ));

    const driver = drivers.map((driver)=>(
        <div>
            <p key={driver.id}>{driver.name}</p>
        </div>
    ));

    console.log(drivers)
    
    //Deconstruct data to send to table component
    let cols;
    let driverInfo = drivers.map((user) => (
        {id: user.id, name: user.name, email: user.email, phone: user.phone, heavy_license: user.heavy_license , status: user.status_code } 
    ))

    if(drivers.length > 0){
        cols = Object.keys(driverInfo[0])
    }
    
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutores</h2>}
        >

            <Head title="Condutores" />

            {drivers && cols && <Table data={driverInfo} columns={cols} editAction="drivers.edit" dataId="user_id"/> }
            
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
            
            <br />
            <strong>Condutores</strong>
            {driver}

        </AuthenticatedLayout>
    );
}