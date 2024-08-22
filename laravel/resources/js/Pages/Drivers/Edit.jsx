import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({ auth, driver }) {

    console.log(driver)

    // Initialize state with driver data
    const [formData, setFormData] = useState({
        name: driver.name,
        email: driver.email,
        phone: driver.phone,
        heavy_license: driver.heavy_license,
        status: driver.status,
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Condutor {driver.id}</h2>}
        >

            {/*<Head title={'Condutor'} />*/}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/drivers/edit/${driver.user_id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="user_id" value={driver.user_id} />

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

                            <p>Licença de Pesados?</p>
                            <input 
                                type="radio" 
                                id="heavy_license_no" 
                                name="heavy_license" 
                                value="0" 
                                checked={formData.heavy_license == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="heavy_vehicle_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="heavy_license_yes" 
                                name="heavy_license" 
                                value="1" 
                                checked={formData.heavy_license == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="heavy_license_yes">Sim</label><br/>

                            <p>Disponível?</p>
                            <input 
                                type="radio" 
                                id="status_no" 
                                name="status" 
                                value="0" 
                                checked={formData.status == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="status_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="status_yes" 
                                name="status" 
                                value="1" 
                                checked={formData.status == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="status_yes">Sim</label><br/>

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>


                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}