<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\VehicleDocument;
use App\Helpers\ErrorMessagesHelper;

class VehicleDocumentController extends Controller
{
    public function index()
    {
        $vehicleDocuments = VehicleDocument::All();

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
        return Inertia::render('VehicleDocuments/NewVehicleDocument');
    }

    public function createVehicleDocument(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        
        try {
            $expired = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 1 : 0;

            $document = VehicleDocument::create([
                'name' => $incomingFields['name'],
                'issue_date' => $incomingFields['issue_date'],
                'expiration_date' => $incomingFields['expiration_date'],
                'expired' => $expired,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->back()->with('message', 'Documento com id ' . $document->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao criar o documento para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleDocumentForm(VehicleDocument $vehicleDoocument)
    {
        return Inertia::render('VehicleDocuments/EditVehicleDocument', ['vehicleDoocument' => $vehicleDoocument]);
    }

    public function editVehicleDocument(VehicleDocument $vehicleDocument, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required','string', 'max: 255'],
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        try {
            $expired = now()->toDateTimeString() > $incomingFields['expiration_date'] ? 1 : 0;

            $document = VehicleDocument::create([
                'name' => $incomingFields['name'],
                'issue_date' => $incomingFields['issue_date'],
                'expiration_date' => $incomingFields['expiration_date'],
                'expired' => $expired,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->back()->with('message', 'Dados do documento com id ' . $document->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao atualizar o documento com id ' . $document->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleDocument($id)
    {
        try {
            $vehicleDocument = VehicleDocument::findOrFail($id);
            $vehicleDocument->delete();
    
            return redirect()->back()->with('message', 'Documento com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Houve um problema ao apagar o documento com id ' . $id . '. Tente novamente.');
        }
    }
}
