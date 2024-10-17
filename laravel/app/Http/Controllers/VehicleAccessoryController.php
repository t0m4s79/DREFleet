<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VehicleAccessory;
use App\Helpers\ErrorMessagesHelper;

class VehicleAccessoryController extends Controller
{
    public function index()
    {
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

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Acessorio com id ' . $accessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o acessorio para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleAccessoryForm(VehicleAccessory $vehicleAccessory)
    {
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

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Dados do acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleAccessory($id)
    {
        try {
            $vehicleAccessory = VehicleAccessory::findOrFail($id);
            $vehicleAccessory->delete();
    
            return redirect()->route('vehicles.documentsAndAccessories', $id)->with('message', 'Acessorio com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.documentsAndAccessories', $id)->with('error', 'Houve um problema ao apagar o acessorio com id ' . $id . '. Tente novamente.');
        }
    }
}
