import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { Button } from '@mui/material';

export default function NewPlace({auth}) {


    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        address: '',
        known_as: '',
        latitude: '',
        longitude: '',
    })

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('places.create'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Nova Morada</h2>}
        >

            <div className='py-12'>
                <div className="max-w-7xl mx-auto my-4 sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className='p-6'>

                            <h2>Criar morada</h2>
                            <form onSubmit={handleSubmit} id="newPlaceForm">
                                <input 
                                    type="hidden" 
                                    name="_token" 
                                    value={csrfToken} 
                                />

                                <label htmlFor="name">Morada</label><br/>
                                <input 
                                    type="text" 
                                    id="address" 
                                    name="address"
                                    value={data.address} 
                                    onChange={(e) => setData('address', e.target.value)} 
                                />
                                {errors.address && <InputError message={errors.address}/>}
                                <br/>

                                <label htmlFor="known_as">Conhecido como</label><br/>
                                <input 
                                    type="text" 
                                    id="known_as" 
                                    name="known_as"
                                    value={data.known_as} 
                                    onChange={(e) => setData('known_as', e.target.value)} 
                                />
                                {errors.known_as && <InputError message={errors.known_as}/>}
                                <br/>

                                <label htmlFor="latitude">Latitude</label><br/>
                                <input 
                                    type="number" 
                                    step=".00001" 
                                    id="latitude" 
                                    name="latitude" 
                                    placeholder="0.00000" 
                                    min="-90" 
                                    max="90"
                                    value={data.latitude} 
                                    onChange={(e) => setData('latitude', e.target.value)} 
                                />
                                {errors.latitude && <InputError message={errors.latitude}/>}
                                <br/>

                                <label htmlFor="longitude">Longitude</label><br/>
                                <input 
                                    type="number" 
                                    step=".00001" 
                                    id="longitude" 
                                    name="longitude" 
                                    placeholder="0.00000" 
                                    min="-180" 
                                    max="180"
                                    value={data.longitude} 
                                    onChange={(e) => setData('longitude', e.target.value)} 
                                />
                                {errors.longitude && <InputError message={errors.longitude}/>}
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
