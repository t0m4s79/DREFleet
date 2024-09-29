<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;

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

    public function createVehicle(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'license_plate' => [
                'required',
                'string',
                'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique(Vehicle::class),
            ],
            'year' => ['required', 'integer', 'digits:4'], // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'heavy_type' => ['required_if:heavy_vehicle,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'wheelchair_adapted' => 'required',
            'wheelchair_certified' => 'required',
            'capacity' => ['required', 'integer', 'min:1'], // Minimum capacity of 1, integer value
            'fuel_consumption' => ['required', 'numeric', 'min:0'], // Numeric value, can't be negative
            'status' => ['required', Rule::in(['Disponível','Indisponível', 'Em manutenção', 'Escondido'])],
            'current_month_fuel_requests' => ['required', 'integer', 'min:0'], // Integer, can’t be negative
            'fuel_type' => ['required', Rule::in(['Gasóleo','Gasolina 95','Gasolina 98','Híbrido','Elétrico'])],
            'current_kilometrage' => ['required', 'integer', 'min:0']
        ], $customErrorMessages);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));

        if($incomingFields['heavy_vehicle'] == '0') {
            $incomingFields['heavy_type'] = null;
        }

        try {
            $vehicle = Vehicle::create($incomingFields);
            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $vehicle->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao criar o veículo. Tente novamente.');
        }
    }

    public function showEditVehicleForm(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/EditVehicle', ['vehicle' => $vehicle]);
    }

    public function editVehicle(Vehicle $vehicle, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'license_plate' => [
                'required',
                'string',
                'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique('vehicles')->ignore($vehicle->id), // Unique rule that ignores the current vehicle's ID during editing
            ],
            'year' => ['required', 'integer', 'digits:4'], // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'heavy_type' => ['required_if:heavy_vehicle,1', Rule::in([null ,'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'wheelchair_adapted' => 'required',
            'wheelchair_certified' => 'required',
            'capacity' => ['required', 'integer', 'min:1'], // Minimum capacity of 1, integer value
            'fuel_consumption' => ['required', 'numeric', 'min:0'], // Numeric value, can't be negative
            'status' => ['required', Rule::in(['Disponível','Indisponível', 'Em manutenção', 'Escondido'])],
            'current_month_fuel_requests' => ['required', 'integer', 'min:0'], // Integer, can’t be negative
            'fuel_type' => ['required', Rule::in(['Gasóleo','Gasolina 95','Gasolina 98','Híbrido','Elétrico'])],
            'current_kilometrage' => ['required', 'integer', 'min:0']
        ], $customErrorMessages);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));

        if($incomingFields['heavy_vehicle'] == '0') {
            $incomingFields['heavy_type'] = null;
        }

        try {
            $vehicle->update($incomingFields);
            return redirect()->route('vehicles.index')->with('message', 'Dados do veículocom id ' . $vehicle->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao atualizar os dados do veículo com id ' . $vehicle->id . '. Tente novamente.');
        }
    }

    public function deleteVehicle($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
    
            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $id . 'apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o veículo com id ' . $id . '. Tente novamente.');
        }
    }
}
