<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VehicleAccessory;
use App\Helpers\ErrorMessagesHelper;

class VehicleAccessoryController extends Controller
{
    public function index()
    {
        $vehicleAccessories = VehicleAccessory::All();

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
        return Inertia::render('VehicleAccessories/NewVehicleAccessory');
    }

    public function createVehicleAccessory(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'condition' => ['required', Rule::in(['Expirado','Danificado','Aceitável'])],
            'expiration_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        
        try {
            $condition = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 'Expirado' : $incomingFields['condition'];

            $accessory = VehicleAccessory::create([
                'name' => $incomingFields['name'],
                'condition' => $condition,
                'expiration_date' => $incomingFields['expiration_date'],
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->back()->with('message', 'Acessorio com id ' . $accessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao criar o acessorio para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleAccessoryForm(VehicleAccessory $vehicleDoocument)
    {
        return Inertia::render('VehicleAccessories/EditVehicleAccessory', ['vehicleDoocument' => $vehicleDoocument]);
    }

    public function editVehicleAccessory(VehicleAccessory $vehicleAccessory, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'condition' => ['required', Rule::in(['Expirado','Danificado','Aceitável'])],
            'expiration_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        try {
            $condition = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 'Expirado' : $incomingFields['condition'];

            $vehicleAccessory->update([
                'name' => $incomingFields['name'],
                'condition' => $condition,
                'expiration_date' => $incomingFields['expiration_date'],
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->back()->with('message', 'Dados do acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao atualizar o acessorio com id ' . $vehicleAccessory->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleAccessory($id)
    {
        try {
            $vehicleAccessory = VehicleAccessory::findOrFail($id);
            $vehicleAccessory->delete();
    
            return redirect()->back()->with('message', 'Acessorio com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao apagar o acessorio com id ' . $id . '. Tente novamente.');
        }
    }
}
