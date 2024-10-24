<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed users page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $users = User::All();

        $users->each(function ($user) {
            $user->created_at = \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i');
            $user->updated_at = \Carbon\Carbon::parse($user->updated_at)->format('d-m-Y H:i');
            $user->phone = $user->phone ?? '-';
        });

        return Inertia::render('Users/AllUsers', [
            'users' => $users,
            'flash' => [
                    'message' => session('message'),
                    'error' => session('error'),
                ],
        ]);
    }

    public function showCreateUserForm()
    {
        Log::channel('user')->info('User accessed user creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        return Inertia::render('Users/NewUser', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
        ]);
    }

    public function createUser(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $request->merge(['email' => strtolower($request->input('email'))]);

        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'lowercase'],
            'phone' => ['nullable', 'numeric', 'digits_between:9,15', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

        $incomingFields['phone'] = $incomingFields['phone'] ?? null;

        try {
            $user = User::create([
                'name' => $incomingFields['name'] ,
                'email' => $incomingFields['email'] ,
                'phone' => $incomingFields['phone'] ,
                'user_type' => 'Nenhum',
                'status' => 'Disponível',
                'password' => Hash::make($incomingFields['password']),
            ]);

            Log::channel('user')->info('User created another user', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'user_id' => $user->id ?? null,
            ]);

            return redirect()->route('users.index')->with('message', 'Utilizador com id ' . $user->id . ' criado com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating user', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('users.index')->with('error', 'Houve um problema ao criar o utilizador. Tente novamente.');
        }
    }

    public function showEditUserForm(User $user)
    {
        Log::channel('user')->info('User accessed user edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'user_id' => $user->id ?? null,
        ]);

        return Inertia::render('Users/EditUser', [
            'user' => $user,
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
        ]);
    }

    public function editUser(User $user, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id), 'lowercase'], // Ignore current user's email,
            'phone' => ['nullable', 'numeric', 'digits_between:9,15', Rule::unique('users', 'phone')->ignore($user->id)],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

        $incomingFields['phone'] = $incomingFields['phone'] ?? null;

        try {
            $user->update($incomingFields);

            Log::channel('user')->info('User edited another user', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'user_id' => $user->id ?? null,
            ]);

            return redirect()->route('users.index')->with('message', 'Dados do/a utilizador/a com id ' . $user->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing user', [
                'user_id' => $user->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('users.index')->with('error', 'Houve um problema ao atualizar os dados do utilizador com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Log::channel('user')->info('User deleted another user', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'user_id' => $id ?? null,
            ]);
    
            return redirect()->route('users.index')->with('message', 'Utilizador com ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting user', [
                'user_id' => $user->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('users.index')->with('error', 'Houve um  problema ao eliminar os dados do utilizador com id ' . $id . '. Tente novamente.');
        }
    }
}
