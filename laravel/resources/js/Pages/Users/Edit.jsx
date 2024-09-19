import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({ auth, user }) {

    // Initialize state with driver data
    const [formData, setFormData] = useState({
        name: user.name,
        email: user.email,
        phone: user.phone,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Utilizador #{user.id}</h2>}
        >

            {/*<Head title={'Utilizador'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/users/edit/${user.id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT"/>
                            <input type="hidden" name="user_id" value={user.id}/>

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

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>


                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}