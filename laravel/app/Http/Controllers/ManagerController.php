<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\RoleUserTypeValidation;

class ManagerController extends Controller
{
    public function index() {
        $managers = User::where('user_type', 'Gestor')->get();

        $managers->each(function ($manager) {
            $manager->created_at = \Carbon\Carbon::parse($manager->created_at)->format('d-m-Y H:i');
            $manager->updated_at = \Carbon\Carbon::parse($manager->updated_at)->format('d-m-Y H:i');
        });

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
            'id' => [
                'required', 
                'exists:users,id',
                new RoleUserTypeValidation(),
            ],
        ], $customErrorMessages);

        $user = User::find($incomingFields['id']);

        try {
            $user->update([
                'user_type' => "Gestor",
            ]);

            return redirect()->route('managers.index')->with('message', 'Gestor/a com id ' . $user->id . ' criado/a com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
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

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);
        
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

        try {
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            return redirect()->route('managers.index')->with('message', 'Dados do/a gestor/a com id ' . $user->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
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
            dd($e);
            return redirect()->route('managers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de gestores. Tente novamente.');
        }
    }

    public function showManagerApprovedOrders(User $user) {
        $orders = Order::where('manager_id', $user->id)->get();
        
        return Inertia::render('Managers/ShowApprovedOrders', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'orders' => $orders,
            'userId' => $user->id,
        ]);
    }

}
