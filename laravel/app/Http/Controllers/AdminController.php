<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ErrorMessagesHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        if(!Gate::allows('view-admin')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed admins page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $admins = User::where('user_type', Roles::ADMIN->value)->get();

        return Inertia::render('Admins/AllAdmins', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'admins' => $admins,
        ]);
    }

    public function showCreateAdminForm()
    {
        if(!Gate::allows('create-admin')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed admin creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $users = User::where('user_type', Roles::NONE->value)->get();

        return Inertia::render('Admins/NewAdmin', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'users' => $users,
        ]);
    }

    public function createAdmin(Request $request)
    {
        if(!Gate::allows('create-admin')) {
            abort(403);
        }

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'id' => [
                'required', 
                'exists:users,id',
                
                function ($attribute, $value, $fail) use ($request) {
                    $user = User::find($value);
        
                    if ($user && $user->user_type != 'Nenhum') {
                        $fail('Somente utilizadores de tipo "Nenhum" podem ser convertidos em Administradores');
                    }
                },

            ],
        ], $customErrorMessages);

        $user = User::find($incomingFields['id']);

        try {
            $user->update([
                'user_type' => Roles::ADMIN->value,
            ]);

            Log::channel('user')->info('User created a admin', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'admin_id' => $user->id ?? null,
            ]);

            return redirect()->route('admins.index')->with('message', 'Administrador/a com id ' . $user->id . ' criado/a com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating admin', [
                'user_id' => $incomingFields['user_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admins.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $user->id . ' à lista de administradores. Tente novamente.');
        }
    }

    public function showEditAdminForm(User $user)
    {
        if(!Gate::allows('edit-admin')) {
            abort(403);
        }

        Log::channel('user')->info('User accessed admin edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'admin_id' => $user->id ?? null,
        ]);

        return Inertia::render('Admins/EditAdmin', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'admin' => $user,
        ]);
    }

    public function editAdmin(User $user, Request $request)
    {
        if(!Gate::allows('edit-admin')) {
            abort(403);
        }

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

            Log::channel('user')->info('User edited an admin', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'admin_id' => $user->id ?? null,
            ]);

            return redirect()->route('admins.index')->with('message', 'Dados do/a administrador/a com id ' . $user->id . ' atualizados com sucesso!');
            
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing admin', context: [
                'route_id' => $user->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admins.index')->with('error', 'Houve um problema ao atualizar os dados do administrador com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteAdmin($id)
    {
        if(!Gate::allows('delete-admin')) {
            abort(403);
        }

        try {
            $user = User::findOrFail($id);
            $user->update([
                'user_type' => Roles::NONE->value,
            ]);

            Log::channel('user')->info('User deleted an admin', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'admin_id' => $id ?? null,
            ]);

            return redirect()->route('admins.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de administradores com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting admin', [
                'route_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admins.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de administradores. Tente novamente.');
        }
    }
}
