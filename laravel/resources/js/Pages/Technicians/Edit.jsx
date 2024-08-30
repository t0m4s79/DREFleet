import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({ auth, technician, associatedKids, addPriority1, addPriority2 }) {

    console.log("tecnico")
    console.log(technician)
    console.log("crianças associadas")
    console.log(associatedKids)
    console.log("pode adicionar com prioridade 1")
    console.log(addPriority1)
    console.log("pode adicionar com prioridade 2")
    console.log(addPriority2)


    // Initialize state with driver data
    const [formData, setFormData] = useState({
        name: technician.name,
        email: technician.email,
        phone: technician.phone,
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle input changes
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prevState => ({
            ...prevState,
            [name]: type === 'radio' ? (checked ? value : prevState[name]) : value,
        }));
    };
    

    return(
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Técnico #{technician.id}</h2>}
        >

            {/*<Head title={'Utilizador'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/Technicians/edit/${technician.id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT"/>
                            <input type="hidden" name="id" value={technician.id}/>

                            <label htmlFor="name">Nome</label><br/>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value={formData.name} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="email">Email</label><br/>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value={formData.email} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="phone">Número de telemóvel</label><br/>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value={formData.phone} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="addPriority1">Adicionar crianças prioritárias (Prioridade 1)</label>

                            <label htmlFor="addPriority2">Adicionar crianças secundárias (Prioridade 2)</label>

                            <label htmlFor="removePriority1">Remover crianças prioritárias (Prioridade 2)</label>

                            <label htmlFor="removePriority1">Remover crianças secundárias (Prioridade 2)</label>

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>


                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}