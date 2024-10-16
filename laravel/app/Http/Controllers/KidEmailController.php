<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Inertia\Inertia;
use App\Models\KidEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;

class KidEmailController extends Controller
{
    public function showCreateKidEmailForm()
    {
        $kids = Kid::all();

        return Inertia::render('KidEmail/NewKidEmail', [
            'kids' => $kids,
        ]);
    }

    public function createKidEmail(Request $request)
    {
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

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Email com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . ' criada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao criar o número de telemóvel. Tente novamente.');
        }
    }

    public function showEditKidEmailForm(KidEmail $kidEmail)
    {
        $kids = Kid::all();

        return Inertia::render('KidEmails/EditKidEmail', [
            'kidEmail' => $kidEmail,
            'kids' => $kids,
        ]);
    }

    public function editKidEmail(KidEmail $kidEmail, Request $request)
    {
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

            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('message', 'Dados do email com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('kids.contacts', $incomingFields['kid_id'])->with('error', 'Houve um problema ao editar os dados do emaill com id ' . $kidEmail->id . ' da criança com id ' . $incomingFields['kid_id'] . '. Tente novamente.');
        }
    }

    public function deleteKidEmail($id)
    {
        try {
            $kidEmail = KidEmail::findOrFail($id);
            $kidEmail->delete();

            return redirect()->route('kids.contacts', $id)->with('message', 'Email com id ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('kids.contacts', $id)->with('error', 'Houve um problema ao apagar o email com id ' . $id . '. Tente novamente.');
        }
    }
}
