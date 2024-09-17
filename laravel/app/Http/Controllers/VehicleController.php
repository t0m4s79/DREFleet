<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    
    public function index()
    {
        $vehicles = Vehicle::All();

        return Inertia::render('Vehicles/AllVehicles', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicles' => $vehicles
        ]);
    }

    public function showCreateVehicleForm()
    {
        return Inertia::render('Vehicles/NewVehicle');
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createVehicle(Request $request)
    {
        $customErrorMessages = [
            'required' => 'O campo :attribute é obrigatório.',
            'license_plate.unique' => 'Já existe um veículo com esta matrícula.',
            'license_plate.regex' => 'A matrícula deve ter no mínimo 2 letras e pode ter até 6 caracteres, aceitando apenas letras e números.',
            'year.integer' => 'O campo ano deve ser um número inteiro.',
            'year.digits' => 'O campo ano deve ter 4 dígitos.',
            'heavy_vehicle.boolean' => 'O campo veículo pesado deve ser verdadeiro ou falso.',
            'wheelchair_adapted.boolean' => 'O campo adaptação para cadeiras de rodas deve ser verdadeiro ou falso.',
            'capacity.integer' => 'O campo capacidade deve ser um número inteiro.',
            'capacity.min' => 'A capacidade deve ser no mínimo :min.',
            'fuel_consumption.numeric' => 'O campo consumo deve ser um número.',
            'status.in' => 'O campo estado deve ser um dos seguintes: ativo, inativo, manutenção.',
            'current_month_fuel_requests.integer' => 'O campo pedidos de reabastecimento deve ser um número inteiro.',
        ];

        $incomingFields = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                //'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique(Vehicle::class),
            ],
            'year' => 'required|integer|digits:4', // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required|integer|min:1', // Minimum capacity of 1, integer value
            'fuel_consumption' => 'required|numeric|min:0', // Numeric value, can't be negative
            'status' => 'required',
            'current_month_fuel_requests' => 'required|integer|min:0', // Integer, can’t be negative
            'oil_type' => 'required',
        ], $customErrorMessages);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));
        $incomingFields['year'] = strip_tags($incomingFields['year']);
        $incomingFields['heavy_vehicle'] = strip_tags($incomingFields['heavy_vehicle']);
        $incomingFields['wheelchair_adapted'] = strip_tags($incomingFields['wheelchair_adapted']);
        $incomingFields['capacity'] = strip_tags($incomingFields['capacity']);
        $incomingFields['fuel_consumption'] = strip_tags($incomingFields['fuel_consumption']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);
        $incomingFields['current_month_fuel_requests'] = strip_tags($incomingFields['current_month_fuel_requests']);
        $incomingFields['oil_type'] = strip_tags($incomingFields['oil_type']);


        Vehicle::create($incomingFields);
        return redirect('/vehicles')->with('message', 'Veículo criado com sucesso!');
    }

    public function showEditScreen(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/Edit', ['vehicle' => $vehicle]);
    }

    public function editVehicle(Vehicle $vehicle, Request $request)
    {
        $customErrorMessages = [
            'required' => 'O campo :attribute é obrigatório.',
            'license_plate.unique' => 'Já existe um veículo com esta matrícula.',
            'license_plate.regex' => 'A matrícula deve ter no mínimo 2 letras e pode ter até 6 caracteres, aceitando apenas letras e números.',
            'year.integer' => 'O campo ano deve ser um número inteiro.',
            'year.digits' => 'O campo ano deve ter 4 dígitos.',
            'heavy_vehicle.boolean' => 'O campo veículo pesado deve ser verdadeiro ou falso.',
            'wheelchair_adapted.boolean' => 'O campo adaptação para cadeiras de rodas deve ser verdadeiro ou falso.',
            'capacity.integer' => 'O campo capacidade deve ser um número inteiro.',
            'capacity.min' => 'A capacidade deve ser no mínimo :min.',
            'fuel_consumption.numeric' => 'O campo consumo deve ser um número.',
            'status.in' => 'O campo estado deve ser um dos seguintes: ativo, inativo, manutenção.',
            'current_month_fuel_requests.integer' => 'O campo pedidos de reabastecimento deve ser um número inteiro.',
        ];

        $incomingFields = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                //'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique('vehicles')->ignore($vehicle->id), // Unique rule that ignores the current vehicle's ID during editing
            ],
            'year' => 'required|integer|digits:4', // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required|integer|min:1', // Minimum capacity of 1, integer value
            'fuel_consumption' => 'required|numeric|min:0', // Numeric value, can't be negative
            'status' => 'required',
            'current_month_fuel_requests' => 'required|integer|min:0', // Integer, can’t be negative
            'oil_type' => 'required',
        ], $customErrorMessages);

        if ($incomingFields['wheelchair_adapted'] == 'Sim') {              //These if's can be taken out if respective attributes methods are taken out of the vehicle model
            $incomingFields['wheelchair_adapted'] = '1';                 //but the the table will show 0 or 1 instead of Sim ou Não
        } else if ($incomingFields['wheelchair_adapted'] == 'Não') {
            $incomingFields['wheelchair_adapted'] = '0';
        }

        if ($incomingFields['heavy_vehicle'] == 'Sim') {
            $incomingFields['heavy_vehicle'] = '1';
        } else if ($incomingFields['heavy_vehicle'] == 'Não') {
            $incomingFields['heavy_vehicle'] = '0';
        }

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));
        $incomingFields['year'] = strip_tags($incomingFields['year']);
        $incomingFields['heavy_vehicle'] = strip_tags($incomingFields['heavy_vehicle']);
        $incomingFields['wheelchair_adapted'] = strip_tags($incomingFields['wheelchair_adapted']);
        $incomingFields['capacity'] = strip_tags($incomingFields['capacity']);
        $incomingFields['fuel_consumption'] = strip_tags($incomingFields['fuel_consumption']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);
        $incomingFields['current_month_fuel_requests'] = strip_tags($incomingFields['current_month_fuel_requests']);
        $incomingFields['oil_type'] = strip_tags($incomingFields['oil_type']);


        try {
            $vehicle->update($incomingFields);
            return redirect('/vehicles')->with('message', 'Dados do veículo editados com sucesso!');
        } catch (\Exception $e) {
            return redirect('kids')->with('error', 'Houve um problema ao editar os dados do veículo. Tente novamente mais tarde.');
        }
    }

    public function deleteVehicle($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect('/vehicles');
    }
}
