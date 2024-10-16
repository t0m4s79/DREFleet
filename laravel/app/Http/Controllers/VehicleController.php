<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    
    public function index()
    {
        $vehicles = Vehicle::All();

        $vehicles->each(function ($vehicle) {
            $vehicle->created_at = \Carbon\Carbon::parse($vehicle->created_at)->format('d-m-Y H:i');
            $vehicle->updated_at = \Carbon\Carbon::parse($vehicle->updated_at)->format('d-m-Y H:i');
            $vehicle->heavy_type = $vehicle->heavy_type ?? '-';
        });

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
            'current_kilometrage' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], $customErrorMessages);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));

        if($incomingFields['heavy_vehicle'] == '0') {
            $incomingFields['heavy_type'] = null;
        } 

        try {
            //TODO: IMAGE RESIZING BEFORE STORING
            //TODO: IMAGE FIELD IN FRONT-END
            if ($request->hasFile('image')) {
                $file = $request->file('image');
        
                // Generate a random name for the image with the original extension
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store the image in the private 'storage/app/vehicles' folder
                $path = $file->storeAs('vehicles', $fileName); // Store in a private folder (not public)

            } else {
                $path = null;
            }

            $vehicle = Vehicle::create([
                'make' => $incomingFields['make'],
                'model' => $incomingFields['model'],
                'license_plate' => $incomingFields['license_plate'],
                'year' => $incomingFields['year'],
                'heavy_vehicle' => $incomingFields['heavy_vehicle'],
                'heavy_type' => $incomingFields['heavy_type'],
                'wheelchair_adapted' => $incomingFields['wheelchair_adapted'],
                'wheelchair_certified' => $incomingFields['wheelchair_certified'],
                'capacity' => $incomingFields['capacity'],
                'fuel_consumption' => $incomingFields['fuel_consumption'],
                'status' => $incomingFields['status'],
                'current_month_fuel_requests' => $incomingFields['current_month_fuel_requests'],
                'fuel_type' => $incomingFields['fuel_type'],
                'current_kilometrage' => $incomingFields['current_kilometrage'],
                'image_path' => $path,
            ]);

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
            'current_kilometrage' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/jpg', 'max:2048'],
        ], $customErrorMessages);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strtoupper(strip_tags($incomingFields['license_plate']));

        if($incomingFields['heavy_vehicle'] == '0') {
            $incomingFields['heavy_type'] = null;
        }

        try {
            //TODO: IMAGE RESIZING BEFORE STORING
            //TODO: IMAGE FIELD IN FRONT-END
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Delete the old image if it exists
                if ($vehicle->image_path) {
                    Storage::disk('local')->delete($vehicle->image_path);
                }
        
                // Generate a random name for the image with the original extension
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store the image in the private 'storage/app/vehicles' folder
                $path = $file->storeAs('vehicles', $fileName); // Store in a private folder

            } else {
                $path = null;
            }

            $vehicle->update([
                'make' => $incomingFields['make'],
                'model' => $incomingFields['model'],
                'license_plate' => $incomingFields['license_plate'],
                'year' => $incomingFields['year'],
                'heavy_vehicle' => $incomingFields['heavy_vehicle'],
                'heavy_type' => $incomingFields['heavy_type'],
                'wheelchair_adapted' => $incomingFields['wheelchair_adapted'],
                'wheelchair_certified' => $incomingFields['wheelchair_certified'],
                'capacity' => $incomingFields['capacity'],
                'fuel_consumption' => $incomingFields['fuel_consumption'],
                'status' => $incomingFields['status'],
                'current_month_fuel_requests' => $incomingFields['current_month_fuel_requests'],
                'fuel_type' => $incomingFields['fuel_type'],
                'current_kilometrage' => $incomingFields['current_kilometrage'],
                'image_path' => $path,
            ]);

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
    
            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o veículo com id ' . $id . '. Tente novamente.');
        }
    }

    public function showVehicleAccessoriesAndDocuments(Vehicle $vehicle)
    {
        // Eager load the 'documents' and 'accessories' relationships
        $vehicle->load('documents', 'accessories');

        // Format the fields for each accessory
        $vehicle->accessories->each(function ($accessory) {
            $accessory->created_at = \Carbon\Carbon::parse($accessory->created_at)->format('d-m-Y H:i');
            $accessory->updated_at = \Carbon\Carbon::parse($accessory->updated_at)->format('d-m-Y H:i');
            $accessory->expiration_date = $accessory->expiration_date ? \Carbon\Carbon::parse($accessory->expiration_date)->format('d-m-Y') : '-';
        }); 

        // Format the fields for each document
        $vehicle->documents->each(function ($document) {
            $document->created_at = \Carbon\Carbon::parse($document->created_at)->format('d-m-Y H:i');
            $document->updated_at = \Carbon\Carbon::parse($document->updated_at)->format('d-m-Y H:i');
            $document->issue_date = \Carbon\Carbon::parse($document->issue_date)->format('d-m-Y');
            $document->expiration_date = \Carbon\Carbon::parse($document->expiration_date)->format('d-m-Y');
            $document->expired = $document->expired ? 'Sim' : 'Não';
        });

        // Return the data to the view with formatted dates
        return Inertia::render('Vehicles/VehicleAccessoriesAndDocuments', [
            'vehicle' => $vehicle
        ]);
    }
}
