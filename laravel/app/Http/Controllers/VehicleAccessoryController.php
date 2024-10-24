<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VehicleAccessory;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;

class VehicleAccessoryController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed vehicles accessories page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicleAccessories = VehicleAccessory::All();

        $vehicleAccessories->each(function ($accessory) {
            $accessory->expiration_date = $accessory->expiration_date ? \Carbon\Carbon::parse($accessory->expiration_date)->format('d-m-Y') : '-';
            $accessory->created_at = \Carbon\Carbon::parse($accessory->created_at)->format('d-m-Y H:i');
            $accessory->updated_at = \Carbon\Carbon::parse($accessory->updated_at)->format('d-m-Y H:i');
        });

        return Inertia::render('VehicleAccessories/AllVehicleAccessories', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicleAccessories' => $vehicleAccessories
        ]);
    }

    public function showCreateVehicleAccessoryForm()
    {
        Log::channel('user')->info('User accessed vehicle accessory creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::all();
        
        return Inertia::render('VehicleAccessories/NewVehicleAccessory', [
            'vehicles' => $vehicles
        ]);
    }

    public function createVehicleAccessory(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'condition' => ['required', Rule::in(['Expirado','Danificado','Aceitável'])],
            'expiration_date' => ['nullable', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        
        try {
            $condition = $incomingFields['expiration_date'] != null && now()->toDateTimeString() > $incomingFields['expiration_date'] ? 'Expirado' : $incomingFields['condition'];

            $accessory = VehicleAccessory::create([
                'name' => $incomingFields['name'],
                'condition' => $condition,
                'expiration_date' => $incomingFields['expiration_date'],
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            Log::channel('user')->info('User created a vehicle accessory', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'accessory_id' => $accessory->id ?? null,
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Acessorio com id ' . $accessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle accessory', [
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o acessorio para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleAccessoryForm(VehicleAccessory $vehicleAccessory)
    {
        Log::channel('user')->info('User accessed vehicle accessory edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'accessory_id' => $vehicleAccessory->id ?? null,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleAccessories/EditVehicleAccessory', [
            'vehicleAccessory' => $vehicleAccessory,
            'vehicles' => $vehicles,
        ]);
    }

    public function editVehicleAccessory(VehicleAccessory $vehicleAccessory, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'condition' => ['required', Rule::in(['Expirado','Danificado','Aceitável'])],
            'expiration_date' => ['nullable', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        try {
            $condition = $incomingFields['expiration_date'] != null && now()->toDateTimeString() > $incomingFields['expiration_date'] ? 'Expirado' : $incomingFields['condition'];

            $vehicleAccessory->update([
                'name' => $incomingFields['name'],
                'condition' => $condition,
                'expiration_date' => $incomingFields['expiration_date'],
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            Log::channel('user')->info('User edited a vehicle accessory', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'accessory_id' => $vehicleAccessory->id ?? null,
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Dados do acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle accessory', [
                'accessory_id' => $vehicleAccessory->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleAccessory($id)
    {
        try {
            $vehicleAccessory = VehicleAccessory::findOrFail($id);
            $vehicleId = $vehicleAccessory->vehicle->id;
            $vehicleAccessory->delete();

            Log::channel('user')->info('User deleted a vehicle accessory', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'accessory_id' => $id ?? null,
                'vehicle_id' => $vehicleId ?? null,
            ]);
    
            return redirect()->route('vehicles.documentsAndAccessories', $vehicleId)->with('message', 'Acessorio com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle accessory', [
                'accessory_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicleAccessories.index')->with('error', 'Houve um problema ao apagar o acessorio com id ' . $id . '. Tente novamente.');
        }
    }
}
