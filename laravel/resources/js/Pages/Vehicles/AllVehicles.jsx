import Table from '@/Components/Table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function AllVehicles( {auth, vehicles}) {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    console.log('vehicles', vehicles)
    let cols;

    if(vehicles.length > 0){
        cols = Object.keys(vehicles[0]);
    }

    const VehicleColumnLabels = {
        id: 'ID',
        make: 'Marca',
        model: 'Modelo',
        license_plate: 'Matricula',
        heavy_vehicle: 'Veiculo Pesado',
        wheelchair_adapted: 'Adapto a Cadeiras de Rodas',
        capacity: 'Capacidade',
        fuel_consumption: 'Consumo',
        status_code: 'Estado',
        current_month_fuel_requests: 'Pedidos de Reabastecimento (Este mes)'
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Veículos</h2>}
        >

            <Head title="Veículos" />

            {vehicles && cols && <Table data={vehicles} columns={cols} columnsLabel={VehicleColumnLabels} editAction="vehicles.showEdit" deleteAction="vehicles.delete"/>}

            <h2>Criar veículo</h2>
            <form action="/vehicles/create" method='POST' id="newVehicleForm">
                <input type="hidden" name="_token" value={csrfToken} />

                <label for="make">Marca</label><br/>
                <input type="text" id="make" name="make"/><br/>

                <label for="model">Modelo</label><br/>
                <input type="text" id="model" name="model"/><br/>

                <label for="license_plate">Matrícula (sem "-")</label><br/>
                <input type="text" minLength="6" maxLength="6" id="license_plate" name="license_plate" placeholder='AAXXBB'
                    pattern="[A-Za-z0-9]+" title="Só são permitidos números e letras" />

                <p>Veículo Pesado?</p>
                <input type="radio" name="heavy_vehicle" value="0"/>
                <label>Não</label><br/>
                <input type="radio" name="heavy_vehicle" value="1"/>
                <label>Sim</label><br/>

                <p>Adaptado a cadeira de rodas?</p>
                <input type="radio" name="wheelchair_adapted" value="0"/>
                <label>Não</label><br/>
                <input type="radio" name="wheelchair_adapted" value="1"/>
                <label>Sim</label><br/>

                <label for="capacity">Capacidade (pessoas):</label><br/>
                <input type="number" id="capacity" name="capacity" min="1" max="100"></input><br/>

                <label for="fuel_consumption">Consumo de combustível (Km/L)</label><br/>
                <input type="number" step=".001" id="fuel_consumption" name="fuel_consumption" placeholder="0.000"></input><br/>

                <p>Mostrar veículo imediatamente como disponível?</p>
                <input type="radio" name="status_code" value="0"/>
                <label>Não</label><br/>
                <input type="radio" name="status_code" value="1"/>
                <label>Sim</label><br/>

                <label for="current_month_fuel_requests">Pedidos de combustível efetuados este mês</label><br/>
                <input type="number" id="current_month_fuel_requests" name="current_month_fuel_requests" min="0" max="100"></input><br/>

                <p><button type="submit" value="Submit">Submeter</button></p>

            </form>
        </AuthenticatedLayout>
    )
}
