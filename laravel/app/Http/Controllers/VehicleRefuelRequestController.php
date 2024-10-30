<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Models\VehicleRefuelRequest;
use App\Notifications\VehicleRefuelRequestNotification;

class VehicleRefuelRequestController extends Controller
{
    public function showCreateVehicleRefuelRequestForm()
    {
        Log::channel('user')->info('User accessed vehicle refuel request creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleRefuelRequests/NewVehicleRefuelRequest', [
            'vehicles' => $vehicles,
        ]);
    }

    public function createVehicleRefuelRequest(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([  
            'date' => ['required', 'date'],
            'quantity' => ['required', 'decimal:0,3', 'min:0'],
            'cost_per_unit' => ['required', 'decimal:0,3', 'min:0'],
            'total_cost' => ['required', 'decimal:0,2', 'min:0'],
            'kilometrage' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', Rule::in(['Gasóleo','Gasolina 95','Gasolina 98','Elétrico'])],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);
        
        try {
            $requestNumber = Vehicle::findOrFail($incomingFields['vehicle_id'])->refuelRequests()->count() + 1;

            if ($requestNumber <= 6) {
                $requestType = 'Normal';
            } else if ($requestNumber <= 10) {
                $requestType = 'Especial';
            } else {
                $requestType = 'Excepcional';
            }

            $request = VehicleRefuelRequest::create([
                'date' => $incomingFields['date'],
                'quantity' => $incomingFields['quantity'],
                'cost_per_unit' => $incomingFields['cost_per_unit'],
                'total_cost' => $incomingFields['total_cost'],
                'kilometrage' => $incomingFields['kilometrage'],
                'fuel_type' => $incomingFields['fuel_type'],
                'request_type' => $requestType,
                'monthly_request_number' => $requestNumber,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            Log::channel('user')->info('User created a vehicle refuel request', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'refuel_request_id' => $request->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            foreach (User::where('user_type', 'Gestor')->get() as $user) {
                $user->notify(new VehicleRefuelRequestNotification($request, Vehicle::find($incomingFields['vehicle_id'])));
            }

            return redirect()->route('vehicles.refuelRequests', $incomingFields['vehicle_id'])->with('message', 'Pedido de reabastecimento com id ' . $request->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle refuel request', [
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.refuelRequests', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o pedido de reabastecimento para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleRefuelRequestForm(VehicleRefuelRequest $vehicleRefuelRequest)
    {
        Log::channel('user')->info('User accessed vehicle refuel request edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'refuel_request_id' => $vehicleRefuelRequest->id,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleRefuelRequests/EditVehicleRefuelRequest', [
            'request' => $vehicleRefuelRequest,
            'vehicles' => $vehicles,
        ]);
    }

    public function editVehicleRefuelRequest(VehicleRefuelRequest $vehicleRefuelRequest, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'date' => ['required', 'date'],
            'quantity' => ['required', 'decimal:0,3', 'min:0'],
            'cost_per_unit' => ['required', 'decimal:0,3', 'min:0'],
            'total_cost' => ['required', 'decimal:0,2', 'min:0'],
            'kilometrage' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', Rule::in(['Gasóleo','Gasolina 95','Gasolina 98','Elétrico'])],
            'request_type' => ['required', 'string' , Rule::in(['Normal', 'Especial', 'Excepcional'])],
            'monthly_request_number' => ['required', 'integer', 'min:1'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        try {
            $vehicleRefuelRequest->update($incomingFields);

            Log::channel('user')->info('User edited a vehicle refuel request', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'refuel_request_id' => $vehicleRefuelRequest->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->route('vehicles.refuelRequests', $incomingFields['vehicle_id'])->with('message', 'Dados do pedido de reabastecimento com id ' . $vehicleRefuelRequest->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle refuel reques', [
                'entry_id' => $vehicleRefuelRequest->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.refuelRequests', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o pedido de reabastecimento com id ' . $vehicleRefuelRequest->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleRefuelRequest($id)
    {
        try {
            $request = VehicleRefuelRequest::findOrFail($id);
            $vehicleId = $request->vehicle->id;
            $request->delete();

            Log::channel('user')->info('User deleted a vehicle refuel request', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'refuel_request_id' => $id,
                'vehicle_id' => $vehicleId,
            ]);
    
            return redirect()->route('vehicles.refuelRequests', $vehicleId)->with('message', 'Pedido de reabastecimento com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle refuel request', [
                'entry_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o pedido de reabastecimento com id ' . $id . '. Tente novamente.');
        }
    }
}
