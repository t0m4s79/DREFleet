import { Head, Link } from '@inertiajs/react';
import LeafletMap from '@/Components/LeafletMap';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import 'leaflet/dist/leaflet.css';

export default function NewOrder({auth, drivers, vehicles, technicians, managers, kids, places}) {

    console.log(drivers);
    console.log(vehicles);
    console.log(technicians);
    console.log(managers);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Novo Pedido</h2>}
        >

            <Head title="Novo Pedido" />
        
            <div className="m-auto py-12 w-4/5">
                <div className="overflow-hidden shadow-lg sm:rounded-lg">
                    <LeafletMap routing={true} onTrajectoryChange={(trajectory) => document.getElementById('trajectory').value = JSON.stringify(trajectory)} />
                </div>

                <div>
                    <form action="/orders/create" method="POST">
                        <input type="hidden" name="_token" value={csrfToken} />
                        
                        <input type="hidden" id="trajectory" name="trajectory"/>

                        <label htmlFor="begin_address">Morada da Origem</label><br/>
                        <input type="text" id="begin_address" name="begin_address"/><br/>

                        <label htmlFor="begin_latitude">Latitude da Origem</label><br />
                        <input 
                            type="number" 
                            id="begin_latitude" 
                            name="begin_latitude" 
                            min="-90" 
                            max="90" 
                            step="0.0000000001"
                            required 
                        /><br/>

                        <label htmlFor="begin_longitude">Longitude da Origem</label><br />
                        <input 
                            type="number" 
                            id="begin_longitude" 
                            name="begin_longitude" 
                            min="-180" 
                            max="180" 
                            step="0.0000000001"
                            required 
                        /><br/>

                        <label htmlFor="end_address">Morada do Destino</label><br/>
                        <input type="text" id="end_address" name="end_address"/><br/>

                        <label htmlFor="end_latitude">Latitude da Origem</label><br />
                        <input 
                            type="number" 
                            id="end_latitude" 
                            name="end_latitude" 
                            min="-90" 
                            max="90" 
                            step="0.0000000001"
                            required 
                        /><br/>

                        <label htmlFor="end_longitude">Longitude da Origem</label><br />
                        <input 
                            type="number" 
                            id="end_longitude" 
                            name="end_longitude" 
                            min="-180" 
                            max="180" 
                            step="0.0000000001"
                            required 
                        /><br/>

                        <label htmlFor="planned_end_date">Data e Hora de Início</label><br/>
                        <input type="datetime-local" id="planned_end_date" name="planned_end_date"/><br/>

                        <label htmlFor="end_date">Data e Hora de Fim</label><br />
                        <input type="datetime-local" id="end_date" name="end_date"/><br/>

                        <label htmlFor="end_address">Tipo de Pedido</label><br/>
                        <input type="text" id="order_type" name="order_type" /><br/>

                        <label htmlFor="vehicle_id">Veículo</label><br />
                        <input type="text" id="vehicle_id" name="vehicle_id"/><br/>

                        <label htmlFor="driver_id">Condutor</label><br />
                        <input type="text" id="driver_id" name="driver_id"/><br/>

                        <label htmlFor="technician_id">Técnico</label><br />
                        <input type="text" id="technician_id" name="technician_id"/><br/>

                        <p><button type="submit" value="Submit">Submeter</button></p>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}