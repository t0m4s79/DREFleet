<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\KidEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Gate;

class KidEmailController extends Controller
{
    public function showCreateKidEmailForm()
    {
        if(! Gate::allows('create-kid')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed kid email creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $kids = Kid::all();

        return Inertia::render('KidEmails/NewKidEmail', [
            'kids' => $kids,
        ]);
    }

    public function createKidEmail(Request $request)
    {
        if(! Gate::allows('create-kid')) {
            abort(403);
        }

        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'email' => ['required', 'email', 'lowercase'],
            'owner_name' => ['required', 'string', 'max:255'],
            'relationship_to_kid' => ['required', 'string', 'max:255'],
            'preference' => ['required', Rule::in(['Preferida', 'Alternativa'])],
            'kid_id' => ['required', 'exists:kids,id'],

        ], $customErrorMessages);

        $incomingFields['owner_name'] = strip_tags($incomingFields['owner_name']);
        $incomingFields['relationship_to_kid'] = strip_tags($incomingFields['relationship_to_kid']);

        try {
            $kidEmail = KidEmail::create($incomingFields);

            Log::channel('user')->info('User created a kid email', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kid_id' => $incomingFields['kid_id'] ?? null,
            ]);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Email com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . ' criada com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating kid email', [
                'kid_id' => $incomingFields['kid_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao criar o número de telemóvel. Tente novamente.');
        }
    }

    public function showEditKidEmailForm(KidEmail $kidEmail)
    {
        if(! Gate::allows('edit-kid')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed kid edit email page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'kid_id' => $kidEmail->id ?? null,
        ]);

        $kids = Kid::all();

        return Inertia::render('KidEmails/EditKidEmail', [
            'kidEmail' => $kidEmail,
            'kids' => $kids,
        ]);
    }

    public function editKidEmail(KidEmail $kidEmail, Request $request)
    {
        if(! Gate::allows('edit-kid')) {
            abort(403);
        }

        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'email' => ['required', 'email', 'lowercase'],
            'owner_name' => ['required', 'string', 'max:255'],
            'relationship_to_kid' => ['required', 'string', 'max:255'],
            'preference' => ['required', Rule::in(['Preferida', 'Alternativa'])],
            'kid_id' => ['required', 'exists:kids,id'],
        ], $customErrorMessages);

        try {
            $kidEmail->update($incomingFields);

            Log::channel('user')->info('User edited a kid email', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kid_id' => $kidEmail->id ?? null,
            ]);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Dados do email com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing kid email', [
                'kid_id' => $incomingFields['kid_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao editar os dados do emaill com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . '. Tente novamente.');
        }
    }

    public function deleteKidEmail($id)
    {
        if(! Gate::allows('delete-kid')) {
            abort(403);
        }

        try {
            $kidEmail = KidEmail::findOrFail($id);
            $kidId = $kidEmail->kid->id;
            $kidEmail->delete();

            Log::channel('user')->info('User deleted a kid email page', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kid_id' => $id ?? null,
            ]);

            return redirect()->route('kids.contacts', $kidId)->with('message', 'Email com id ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting kid email', [
                'kid_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('kids.index')->with('error', 'Houve um problema ao apagar o email com id ' . $id . '. Tente novamente.');
        }
    }
}
