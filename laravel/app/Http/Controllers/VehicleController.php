<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
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

    public function createVehicle(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique(Vehicle::class),
            ],
            'year' => 'required|integer|digits:4', // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required|integer|min:1', // Minimum capacity of 1, integer value
            'fuel_consumption' => 'required|numeric|min:0', // Numeric value, can't be negative
            'status' => 'required',
            'current_month_fuel_requests' => 'required|integer|min:0', // Integer, can’t be negative
            'fuel_type' => 'required',
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
        $incomingFields['fuel_type'] = strip_tags($incomingFields['fuel_type']);

        try {
            $vehicle = Vehicle::create($incomingFields);
            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $vehicle->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao criar o veículo. Tente novamente.');
        }
    }

    public function showEditVehicleForm(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/Edit', ['vehicle' => $vehicle]);
    }

    public function editVehicle(Vehicle $vehicle, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                'regex:/^(?=.*[a-zA-Z]{2})[a-zA-Z0-9]{2,6}$/', // Must have at least 2 letters and can contain up to 6 alphanumeric characters
                Rule::unique('vehicles')->ignore($vehicle->id), // Unique rule that ignores the current vehicle's ID during editing
            ],
            'year' => 'required|integer|digits:4', // Ensure the year is a 4-digit integer
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required|integer|min:1', // Minimum capacity of 1, integer value
            'fuel_consumption' => 'required|numeric|min:0', // Numeric value, can't be negative
            'status' => 'required',
            'current_month_fuel_requests' => 'required|integer|min:0', // Integer, can’t be negative
            'fuel_type' => 'required',
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
        $incomingFields['fuel_type'] = strip_tags($incomingFields['fuel_type']);


        try {
            $vehicle->update($incomingFields);
            return redirect()->route('vehicles.index')->with('message', 'Dados do veículocom id ' . $vehicle->id . ' atualizados com sucesso!');
        } catch (\Exception $e) {
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
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o veículo com id ' . $id . '. Tente novamente.');
        }
    }
}
