import React, { useState } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Edit({ auth, vehicle}) {
    // Initialize state with vehicle data
    const [formData, setFormData] = useState({
        make: vehicle.make,
        model: vehicle.model,
        license_plate: vehicle.license_plate,
        heavy_vehicle: vehicle.heavy_vehicle,
        wheelchair_adapted: vehicle.wheelchair_adapted,
        capacity: vehicle.capacity,
        fuel_consumption: vehicle.fuel_consumption,
        status_code: vehicle.status_code,
        current_month_fuel_requests: vehicle.current_month_fuel_requests,
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

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículo {vehicle.id}</h2>}
        >

            {/* Optional: Head component to set the page title */}
            {/* <Head title={`Edit Vehicle ${vehicle.id}`} /> */}

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form action={`/vehicles/edit/${vehicle.id}`} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />

                            <label htmlFor="make">Marca</label><br/>
                            <input 
                                type="text" 
                                id="make" 
                                name="make" 
                                value={formData.make} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="model">Modelo</label><br/>
                            <input 
                                type="text" 
                                id="model" 
                                name="model" 
                                value={formData.model} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="license_plate">Matrícula (sem "-")</label><br/>
                            <input 
                                type="text" 
                                minLength="6" 
                                maxLength="6" 
                                id="license_plate" 
                                name="license_plate" 
                                placeholder='AAXXBB' 
                                value={formData.license_plate}
                                onChange={handleChange}
                                pattern="[A-Za-z0-9]+" 
                                title="Só são permitidos números e letras"
                                className="mt-1 block w-full"
                            />

                            <p>Veículo Pesado?</p>
                            <input 
                                type="radio" 
                                id="heavy_vehicle_no" 
                                name="heavy_vehicle" 
                                value="0" 
                                checked={formData.heavy_vehicle == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="heavy_vehicle_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="heavy_vehicle_yes" 
                                name="heavy_vehicle" 
                                value="1" 
                                checked={formData.heavy_vehicle == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="heavy_vehicle_yes">Sim</label><br/>

                            <p>Adaptado a cadeira de rodas?</p>
                            <input 
                                type="radio" 
                                id="wheelchair_no" 
                                name="wheelchair_adapted" 
                                value="0" 
                                checked={formData.wheelchair_adapted == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="wheelchair_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="wheelchair_yes" 
                                name="wheelchair_adapted" 
                                value="1" 
                                checked={formData.wheelchair_adapted == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="wheelchair_yes">Sim</label><br/>

                            <label htmlFor="capacity">Capacidade (pessoas):</label><br/>
                            <input 
                                type="number" 
                                id="capacity" 
                                name="capacity" 
                                min="1" 
                                max="100" 
                                value={formData.capacity} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <label htmlFor="fuel_consumption">Consumo de combustível (Km/L)</label><br/>
                            <input 
                                type="number" 
                                step=".001" 
                                id="fuel_consumption" 
                                name="fuel_consumption" 
                                placeholder="0.000" 
                                value={formData.fuel_consumption} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <p>Mostrar veículo imediatamente como disponível?</p>
                            <input 
                                type="radio" 
                                id="status_code_no" 
                                name="status_code" 
                                value="0" 
                                checked={formData.status_code == "0"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="status_code_no">Não</label><br/>
                            <input 
                                type="radio" 
                                id="status_code_yes" 
                                name="status_code" 
                                value="1" 
                                checked={formData.status_code == "1"} 
                                onChange={handleChange}
                            />
                            <label htmlFor="status_code_yes">Sim</label><br/>

                            <label htmlFor="current_month_fuel_requests">Pedidos de combustível efetuados este mês</label><br/>
                            <input 
                                type="number" 
                                id="current_month_fuel_requests" 
                                name="current_month_fuel_requests" 
                                min="0" 
                                max="100" 
                                value={formData.current_month_fuel_requests} 
                                onChange={handleChange}
                                className="mt-1 block w-full"
                            /><br/>

                            <p><button type="submit" className="bg-blue-500 text-white py-2 px-4 rounded">Submeter</button></p>

                        </form>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
