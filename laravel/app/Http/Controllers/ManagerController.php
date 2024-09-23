<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index() {
        $managers = User::where('user_type', 'Gestor')->get();

        return Inertia::render('Managers/AllManagers', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'managers' => $managers,
        ]);
    }

    public function showCreateManagerForm() {
        $users = User::where('user_type', 'Nenhum')->get();

        return Inertia::render('Managers/NewManager', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'users' => $users,
        ]);
    }

    public function createManager(Request $request) {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        
        $incomingFields = $request->validate([
            'id' => ['required', 'exists:users,id'],
        ], $customErrorMessages);

        $incomingFields['id'] = strip_tags($incomingFields['id']);

        $user = User::find($incomingFields['id']);

        if ($user->user_type != 'Nenhum') {
            return redirect('/managers')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em gestores.');
        }

        try {
            $user->update([
                'user_type' => "Gestor",
            ]);

            return redirect()->route('managers.index')->with('message', 'Gestor/a com id ' . $user->id . ' criado/a com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('managers.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $user->id . ' à lista de gestores. Tente novamente.');
        }
    }

    public function showEditManagerForm(User $user) {
        return Inertia::render('Managers/EditManager', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'manager' => $user,
        ]);
    }

    public function editManager(User $user, Request $request) {
        //dd($request);

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => 'required',
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);

        try {
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            return redirect()->route('managers.index')->with('message', 'Dados do/a gestor/a com id ' . $user->id . ' atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('managers.index')->with('error', 'Houve um problema ao atualizar os dados do gestor com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteManager($id) {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            return redirect()->route('managers.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de gestores com sucesso!');

        } catch (\Exception $e) {
            return redirect()->route('managers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de gestores. Tente novamente.');
        }
    }

    //TODO: VERIFICATION MESSAGES

    //TODO: MANAGERS FRONTEND TABLE SHOULD HAVE LINK FOR ALL APPROVED ORDERS BY HIM

    //TODO: DASHBOARD TESTS

    //TODO: ERROR HELPER UNIT TESTS

    //TODO: FIX TECHNICIAN FAILLING TEST

    //TODO: IMPLEMENT DASHBOARD AND ORDER TESTS

    //TODO: IN ALL CONTROLLERS -> CHECK IF EVERY FIELDS SHOULD HAVE A STRIP TAGS
}
