<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\KidPhoneNumber;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;

class KidPhoneNumberController extends Controller
{
    public function showCreateKidPhoneNumberForm()
    {
        $kids = Kid::all();

        return Inertia::render('KidPhoneNumbers/NewKidPhoneNumber', [
            'kids' => $kids,
        ]);
    }
    
    public function createKidPhoneNumber(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'owner_name' => ['required', 'string', 'max:255'],
            'relationship_to_kid' => ['required', 'string', 'max:255'],
            'preference' => ['required', Rule::in(['Preferida', 'Alternativa'])],
            'kid_id' => ['required', 'exists:kids,id'],

        ], $customErrorMessages);

        $incomingFields['owner_name'] = strip_tags($incomingFields['owner_name']);
        $incomingFields['relationship_to_kid'] = strip_tags($incomingFields['relationship_to_kid']);
        
        try {
            $kidPhoneNumber = KidPhoneNumber::create($incomingFields);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Número de telemóvel com id ' . $kidPhoneNumber->id . ' da criança com id ' . $incomingFields['kid_id'] . ' criada com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating kid phone number', [
                'kid_id' => $incomingFields['kid_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao criar o número de telemóvel. Tente novamente.');
        }
    }

    public function showEditKidPhoneNumberForm(KidPhoneNumber $kidPhoneNumber)
    {
        $kids = Kid::all();

        return Inertia::render('KidPhoneNumbers/EditKidPhoneNumber', [
            'kidPhoneNumber' => $kidPhoneNumber,
            'kids' => $kids,
        ]);
    }

    public function editKidPhoneNumber(KidPhoneNumber $kidPhoneNumber, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'owner_name' => ['required', 'string', 'max:255'],
            'relationship_to_kid' => ['required', 'string', 'max:255'],
            'preference' => ['required', Rule::in(['Preferida', 'Alternativa'])],
            'kid_id' => ['required', 'exists:kids,id'],
        ], $customErrorMessages);

        try {
            $kidPhoneNumber->update($incomingFields);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Dados do número de telemóvel com id ' . $kidPhoneNumber->id . ' da criança com id ' . $incomingFields['kid_id'] . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing kid phone number', [
                'kid_id' => $incomingFields['kid_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao editar os dados do número de telemóvel com id ' . $kidPhoneNumber->id . ' da criança com id ' . $incomingFields['kid_id'] . '. Tente novamente.');
        }
    }

    public function deleteKidPhoneNumber($id)
    {
        try {
            $kidPhoneNumber = KidPhoneNumber::findOrFail($id);
            $kidId = $kidPhoneNumber->kid->id;
            $kidPhoneNumber->delete();

            return redirect()->route('kids.contacts', $kidId)->with('message', 'Número de telemóvel com id ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting kid phone number', [
                'kid_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.index')->with('error', 'Houve um problema ao apagar o número de telemóvel com id ' . $id . '. Tente novamente.');
        }
    }
}
