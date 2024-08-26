import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@mui/material';
import { useState } from 'react';

export default function NewDriver( {auth,vehicle} ) {

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        make: '',
        model: '',
        license_plate: '',
        heavy_vehicle: '0',
        wheelchair_adapted: '0',
        capacity: '9',
        fuel_consumption: '',
        status: 'Disponível',
        current_month_fuel_requests: '0'
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vehicles.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Veículo</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar veículo</h2>
                            <form onSubmit={handleSubmit} id="newVehicleForm">
                                <input type="hidden" name="_token" value={csrfToken} />

                                <label htmlFor="make">Marca</label><br/>
                                <input 
                                    type="text" 
                                    id="make" 
                                    name="make" 
                                    value={data.make} 
                                    onChange={(e) => setData('make', e.target.value)} 
                                />
                                {errors.make && <InputError message={errors.make}/>}
                                <br/>

                                <label htmlFor="model">Modelo</label><br/>
                                <input 
                                    type="text" 
                                    id="model" 
                                    name="model" 
                                    value={data.model} 
                                    onChange={(e) => setData('model', e.target.value)} 
                                />
                                {errors.model && <InputError message={errors.model}/>}
                                <br/>

                                <label htmlFor="license_plate">Matrícula (sem "-")</label><br/>
                                <input 
                                    type="text" 
                                    minLength="6" 
                                    maxLength="6" 
                                    id="license_plate" 
                                    name="license_plate" 
                                    placeholder='AAXXBB'
                                    pattern="[A-Za-z0-9]+" 
                                    title="Só são permitidos números e letras"
                                    value={data.license_plate} 
                                    onChange={(e) => setData('license_plate', e.target.value)} 
                                />
                                {errors.license_plate && <InputError message={errors.license_plate}/>}
                                <br/>

                                <p>Veículo Pesado?</p>
                                <input 
                                    type="radio" 
                                    name="heavy_vehicle" 
                                    value="0" 
                                    checked={data.heavy_vehicle === '0'}
                                    onChange={(e) => setData('heavy_vehicle', e.target.value)} 
                                />
                                <label>Não</label><br/>
                                <input 
                                    type="radio" 
                                    name="heavy_vehicle" 
                                    value="1"
                                    checked={data.heavy_vehicle === '1'} 
                                    onChange={(e) => setData('heavy_vehicle', e.target.value)} 
                                />
                                <label>Sim</label>
                                {errors.heavy_vehicle && <InputError message={errors.heavy_vehicle}/>}
                                <br/>

                                <p>Adaptado a cadeira de rodas?</p>
                                <input 
                                    type="radio" 
                                    name="wheelchair_adapted" 
                                    value="0"
                                    checked={data.wheelchair_adapted === '0'} 
                                    onChange={(e) => setData('wheelchair_adapted', e.target.value)} 
                                />
                                <label>Não</label><br/>
                                <input 
                                    type="radio" 
                                    name="wheelchair_adapted" 
                                    value="1"
                                    checked={data.wheelchair_adapted === '1'} 
                                    onChange={(e) => setData('wheelchair_adapted', e.target.value)} 
                                />
                                <label>Sim</label>
                                {errors.wheelchair_adapted && <InputError message={errors.wheelchair_adapted}/>}
                                <br/>

                                <label htmlFor="capacity">Capacidade (pessoas):</label><br/>
                                <input 
                                    type="number" 
                                    id="capacity" 
                                    name="capacity" 
                                    min="1" 
                                    max="100"
                                    value={data.capacity}
                                    onChange={(e) => setData('capacity', e.target.value)} 
                                />
                                {errors.capacity && <InputError message={errors.capacity}/>}
                                <br/>

                                <label htmlFor="fuel_consumption">Consumo de combustível (L/100Km)</label><br/>
                                <input 
                                    type="number" 
                                    step=".001" 
                                    id="fuel_consumption" 
                                    name="fuel_consumption" 
                                    placeholder="0.000"
                                    value={data.fuel_consumption}
                                    onChange={(e) => setData('fuel_consumption', e.target.value)} 
                                />
                                {errors.fuel_consumption && <InputError message={errors.fuel_consumption}/>}
                                <br/>

                                <p>Estado do Veículo:</p>
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="Disponível"
                                    checked={data.status === 'Disponível'} 
                                    onChange={(e) => setData('status', e.target.value)} 
                                />
                                <label>Disponível</label><br/>
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="Indisponível"
                                    checked={data.status === 'Indisponível'} 
                                    onChange={(e) => setData('status', e.target.value)} 
                                />
                                <label>Indisponível</label><br/>
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="Em manutenção"
                                    checked={data.status === 'Em manutenção'} 
                                    onChange={(e) => setData('status', e.target.value)} 
                                />
                                <label>Em manutenção</label><br/>
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="Escondido"
                                    checked={data.status === 'Escondido'} 
                                    onChange={(e) => setData('status', e.target.value)} 
                                />
                                <label>Escondido</label>
                                {errors.status && <InputError message={errors.status}/>}
                                <br/>

                                <label htmlFor="current_month_fuel_requests">Pedidos de combustível efetuados este mês</label><br/>
                                <input 
                                    type="number" 
                                    id="current_month_fuel_requests" 
                                    name="current_month_fuel_requests" 
                                    min="0" 
                                    max="100"
                                    value={data.current_month_fuel_requests}
                                    onChange={(e) => setData('current_month_fuel_requests', e.target.value)} 
                                />
                                {errors.current_month_fuel_requests && <InputError message={errors.current_month_fuel_requests}/>}
                                <br/>

                                <Button variant="outlined" type="submit" value="Submit">Submeter</Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-gray-600">Guardado</p>
                                </Transition>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )

}