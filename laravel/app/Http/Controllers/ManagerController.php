<?php

namespace App\Http\Controllers;

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
        $incomingFields = $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);

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

    public function showEditScreen(User $user) {
        return Inertia::render('Managers/Edit', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'manager' => $user,
        ]);
    }

    public function editManager(User $user, Request $request) {
        //dd($request);
        $incomingFields = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'status' => 'required',
        ]);
        
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
            return redirect()->back()->with('error', 'Houve um problema ao editar os dados do gestor. Tente novamente.');
        }
    }

    public function deleteManager($id) {
        $user = User::findOrFail($id);
        $user->update([
            'user_type' => "Nenhum",
        ]);

        return redirect('/managers');
    }

    //TODO: VERIFICATION MESSAGES

    //TODO: MANAGER TESTS

    //TODO: ORDER TESTS

    //TODO: REFACTOR ERROR MESSAGES IN BACKEND TO A COMMON FUNCTION INSTEAD OF REPEATING IN EVERY CRUD FUNCTION

    //TODO: JSX COMMENTS!!!!!!!!!!!!!

    //TODO: CHANGE ORDER OF EDIT FUNCTIONS IN CONTROLLERS

    //TODO: DELETE FUNCTIONS SNACK BAR MESSAGE
}
