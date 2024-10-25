<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\VehicleDocumentDataValidation;

class VehicleDocumentController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed vehicle documents page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicleDocuments = VehicleDocument::All();

        $vehicleDocuments->each(function ($document) {
            $document->issue_date = \Carbon\Carbon::parse($document->issue_date)->format('d-m-Y');
            $document->expiration_date = \Carbon\Carbon::parse($document->expiration_date)->format('d-m-Y');
            $document->created_at = \Carbon\Carbon::parse($document->created_at)->format('d-m-Y H:i');
            $document->updated_at = \Carbon\Carbon::parse($document->updated_at)->format('d-m-Y H:i');
            $document->expired = $document->expired ? 'Sim' : 'Não';
        });

        return Inertia::render('VehicleDocuments/AllVehicleDocuments', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'vehicleDocuments' => $vehicleDocuments
        ]);
    }

    public function showCreateVehicleDocumentForm()
    {
        Log::channel('user')->info('User accessed vehicle document creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleDocuments/NewVehicleDocument', [
            'vehicles' => $vehicles,
        ]);
    }

    public function createVehicleDocument(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after:issue_date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'data' => [
                'nullable',
                'array',
                new VehicleDocumentDataValidation(),
            ],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        // Ensure $incomingFields['data'] is an array
        $formattedData = isset($incomingFields['data']) ? $incomingFields['data'] : [];

        // Iterate through each key-value pair in the additional data
        foreach ($formattedData as $key => $value) {
            $formattedData[strip_tags($key)] = strip_tags($value);
        }
        
        try {
            $expired = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 1 : 0;

            $document = VehicleDocument::create([
                'name' => $incomingFields['name'],
                'issue_date' => $incomingFields['issue_date'],
                'expiration_date' => $incomingFields['expiration_date'],
                'expired' => $expired,
                'vehicle_id' => $incomingFields['vehicle_id'],
                'data' => $formattedData != [] ? $formattedData : null,
            ]);

            Log::channel('user')->info('User created a vehicle document', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'document_id' => $document->id ?? null,
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Documento com id ' . $document->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle document', [
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o documento para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleDocumentForm(VehicleDocument $vehicleDocument)
    {
        Log::channel('user')->info('User accessed vehicle document edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'document_id' => $vehicleDocument->id ?? null,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleDocuments/EditVehicleDocument', [
            'vehicleDocument' => $vehicleDocument,
            'vehicles' => $vehicles,
        ]);
    }

    public function editVehicleDocument(VehicleDocument $vehicleDocument, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after:issue_date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'data' => [
                'nullable',
                'array',
                new VehicleDocumentDataValidation(),
            ],        
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        // Ensure $incomingFields['data'] is an array
        $formattedData = isset($incomingFields['data']) ? $incomingFields['data'] : [];

        // Iterate through each key-value pair in the additional data
        foreach ($formattedData as $key => $value) {
            $formattedData[strip_tags($key)] = strip_tags($value);
        }

        try {
            $expired = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 1 : 0;

            $vehicleDocument->update([
                'name' => $incomingFields['name'],
                'issue_date' => $incomingFields['issue_date'],
                'expiration_date' => $incomingFields['expiration_date'],
                'expired' => $expired,
                'vehicle_id' => $incomingFields['vehicle_id'],
                'data' => $formattedData != [] ? $formattedData : null,
            ]);

            Log::channel('user')->info('User edited a vehicle document', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'document_id' => $vehicleDocument->id ?? null,
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('message', 'Dados do documento com id ' . $vehicleDocument->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle document', [
                'document_id' => $vehicleDocument->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.documentsAndAccessories', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o documento com id ' . $vehicleDocument->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleDocument($id)
    {
        try {
            $vehicleDocument = VehicleDocument::findOrFail($id);
            $vehicleId = $vehicleDocument->vehicle->id;
            $vehicleDocument->delete();

            Log::channel('user')->info('User deleted a vehicle document', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'document_id' => $id ?? null,
                'vehicle_id' => $vehicleId ?? null,
            ]);
    
            return redirect()->route('vehicles.documentsAndAccessories', $vehicleId)->with('message', 'Documento com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle document', [
                'document_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicleDocuments.index')->with('error', 'Houve um problema ao apagar o documento com id ' . $id . '. Tente novamente.');
        }
    }
}
