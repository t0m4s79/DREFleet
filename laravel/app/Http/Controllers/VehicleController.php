<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    
    public function index()
    {
        Log::channel('user')->info('User accessed vehicles page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::withCount([
            'orders as this_year_tow_counts' => function ($query) {
                $query->whereYear('expected_begin_date', now()->year)
                      ->whereHas('occurrences', function ($query) {
                          $query->where('vehicle_towed', 1);
                      });
            }
        ])->get();

        $vehicles->each(function ($vehicle) {
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
        if(! Gate::allows('create-vehicle')){
            abort(403);
        };

        Log::channel('user')->info('User accessed vehicle creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        return Inertia::render('Vehicles/NewVehicle');
    }

    public function createVehicle(Request $request)
    {
        if(! Gate::allows('create-vehicle')){
            abort(403);
        };

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
            'heavy_vehicle' => ['required', 'boolean'],
            'heavy_type' => ['required_if:heavy_vehicle,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'wheelchair_adapted' => ['required', 'boolean'],
            'wheelchair_certified' => ['required', 'boolean'],
            'tcc' => ['required', 'boolean'],
            'yearly_allowed_tows' => ['required', 'integer', 'min:0'],
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
                'tcc' => $incomingFields['tcc'],
                'yearly_allowed_tows' => $incomingFields['yearly_allowed_tows'],
                'capacity' => $incomingFields['capacity'],
                'fuel_consumption' => $incomingFields['fuel_consumption'],
                'status' => $incomingFields['status'],
                'current_month_fuel_requests' => $incomingFields['current_month_fuel_requests'],
                'fuel_type' => $incomingFields['fuel_type'],
                'current_kilometrage' => $incomingFields['current_kilometrage'],
                'image_path' => $path ?? null,
            ]);

            Log::channel('user')->info('User created a vehicle', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'vehicle_id' => $vehicle->id ?? null,
            ]);

            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $vehicle->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao criar o veículo. Tente novamente.');
        }
    }

    public function showEditVehicleForm(Vehicle $vehicle)
    {
        if(! Gate::allows('edit-vehicle', $vehicle)){
            abort(403);
        };

        Log::channel('user')->info('User accessed vehicle edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'vehicle_id' => $vehicle->id ?? null,
        ]);

        return Inertia::render('Vehicles/EditVehicle', ['vehicle' => $vehicle]);
    }

    public function editVehicle(Vehicle $vehicle, Request $request)
    {
        if(! Gate::allows('edit-vehicle', $vehicle)){
            abort(403);
        };

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
            'heavy_vehicle' => ['required', 'boolean'],
            'heavy_type' => ['required_if:heavy_vehicle,1', Rule::in([null ,'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'wheelchair_adapted' => ['required', 'boolean'],
            'wheelchair_certified' => ['required', 'boolean'],
            'tcc' => ['required', 'boolean'],
            'yearly_allowed_tows' => ['required', 'integer', 'min:0'],
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
                'tcc' => $incomingFields['tcc'],
                'yearly_allowed_tows' => $incomingFields['yearly_allowed_tows'],
                'capacity' => $incomingFields['capacity'],
                'fuel_consumption' => $incomingFields['fuel_consumption'],
                'status' => $incomingFields['status'],
                'current_month_fuel_requests' => $incomingFields['current_month_fuel_requests'],
                'fuel_type' => $incomingFields['fuel_type'],
                'current_kilometrage' => $incomingFields['current_kilometrage'],
                'image_path' => $path ?? null,
            ]);

            Log::channel('user')->info('User edited a vehicle', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'vehicle_id' => $vehicle->id ?? null,
            ]);

            return redirect()->route('vehicles.index')->with('message', 'Dados do veículocom id ' . $vehicle->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle', [
                'vehicle_id' => $vehicle->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao atualizar os dados do veículo com id ' . $vehicle->id . '. Tente novamente.');
        }
    }

    public function deleteVehicle($id)
    {
        if(! Gate::allows('delete-vehicle')){
            abort(403);
        };

        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();

            Log::channel('user')->info('User deleted a vehicle', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'vehicle_id' => $id ?? null,
            ]);
    
            return redirect()->route('vehicles.index')->with('message', 'Veículo com id ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle', [
                'vehicle_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o veículo com id ' . $id . '. Tente novamente.');
        }
    }

    public function showVehicleAccessoriesAndDocuments(Vehicle $vehicle)
    {
        Log::channel('user')->info('User accessed a vehicle accessories and documents page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'vehicle_id' => $vehicle->id ?? null,
        ]);

        // Eager load the 'documents' and 'accessories' relationships
        $vehicle->load('documents', 'accessories');

        // Format the fields for each accessory
        $vehicle->accessories->each(function ($accessory) {
            $accessory->expiration_date = $accessory->expiration_date ? Carbon::parse($accessory->expiration_date)->format('d-m-Y') : '-';
        }); 

        // Format the fields for each document
        $vehicle->documents->each(function ($document) {
            $document->issue_date = Carbon::parse($document->issue_date)->format('d-m-Y');
            $document->expiration_date = Carbon::parse($document->expiration_date)->format('d-m-Y');
            $document->expired = $document->expired ? 'Sim' : 'Não';
        });

        // Return the data to the view with formatted dates
        return Inertia::render('Vehicles/VehicleAccessoriesAndDocuments', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicle' => $vehicle
        ]);
    }

    public function showVehicleKilometrageReports(Vehicle $vehicle)
    {
        if(! Gate::allows('view-vehicle-report')){
            abort(403);
        };

        Log::channel('user')->info('User accessed a vehicle kilometrage reports page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'vehicle_id' => $vehicle->id ?? null,
        ]);

        $vehicle->load('kilometrageReports');

        // Format the fields for each report entry
        $vehicle->kilometrageReports->each(function ($report) {
            $report->date = Carbon::parse($report->date)->format('d-m-Y');
        });
        
        return Inertia::render('Vehicles/VehicleKilometrageReports', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicle' => $vehicle
        ]);
    }

    public function showVehicleRefuelRequests(Vehicle $vehicle)
    {
        if(! Gate::allows('view-vehicle-refuel-request')){
            abort(403);
        };
        
        Log::channel('user')->info('User accessed a vehicle refuel requests page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'vehicle_id' => $vehicle->id ?? null,
        ]);

        $vehicle->load('refuelRequests');

        // Format the fields for each report entry
        $vehicle->kilometrageReports->each(function ($request) {
            $request->date = Carbon::parse($request->date)->format('d-m-Y');
        });
        
        return Inertia::render('Vehicles/VehicleRefuelRequests', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicle' => $vehicle
        ]);
    }

    public function showVehicleMaintenanceReports(Vehicle $vehicle)
    {
        if(! Gate::allows('view-vehicle-report')){
            abort(403);
        };

        Log::channel('user')->info('User accessed a vehicle maintenance reports page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'vehicle_id' => $vehicle->id ?? null,
        ]);

        $vehicle->load('maintenanceReports');

        // Format the fields for each report entry
        $vehicle->maintenanceReports->each(function ($request) {
            $request->begin_date = Carbon::parse($request->issue_date)->format('d-m-Y');
            $request->end_date = Carbon::parse($request->expiration_date)->format('d-m-Y');
        });
        
        return Inertia::render('Vehicles/VehicleMaintenanceReports', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicle' => $vehicle
        ]);
    }
}
