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
            return redirect('/managers')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em técnicos.');
        }

        try {
            $user->update([
                'user_type' => "Gestor",
            ]);

            return redirect('/managers')->with('message', 'Gestor/a criado/a com sucesso!');
        } catch (\Exception $e) {
            return redirect('managers')->with('error', 'Houve um problema ao criar o gestor. Tente novamente.');
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

            return redirect('/managers')->with('message', 'Dados do/a gestor/a atualizados com sucesso!');
        } catch (\Exception $e) {
            return redirect('/managers')->with('error', 'Houve um problema ao editar os dados do gestor. Tente novamente.');
        }
    }

    public function deleteManager($id) {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            return redirect('/managers')->with('message', 'Utilizador retirado da lista de gestores com sucesso!');

        } catch (\Exception $e) {
            return redirect('/managers')->with('error', 'Houve um problema ao retirar o utilizador da lista de gestores. Tente novamente.');
        }
    }

    //TODO: VERIFICATION MESSAGES

    //TODO: MANAGER TESTS

    //TODO: ORDER TESTS

    //TODO: REFACTOR ERROR MESSAGES IN BACKEND TO A COMMON FUNCTION INSTEAD OF REPEATING IN EVERY CRUD FUNCTION

    //TODO: :attribute IN PORTUGUESE!!!

    //TODO: JSX COMMENTS!!!!!!!!!!!!!

    //TODO: CHANGE ORDER OF EDIT FUNCTIONS IN CONTROLLERS

    //TODO: DELETE FUNCTIONS SNACK BAR MESSAGE

    //TODO: MANAGERS FRONTEND TABLE SHOULD HAVE LINK FOR ALL APPROVED ORDERS BY HIM

    //TODO: DASHBOARD

    //TODO: DASHBOARD TESTS

    //TODO: SUCESS MESSAGES SHOWING ID
}
