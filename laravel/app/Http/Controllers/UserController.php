<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::All();

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

            return redirect()->route('users.index')->with('message', 'Utilizador com id ' . $user->id . ' criado com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('users.index')->with('error', 'Houve um problema ao criar o utilizador. Tente novamente.');
        }
    }

    public function showEditUserForm(User $user)
    {
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
            return redirect()->route('users.index')->with('message', 'Dados do/a utilizador/a com id ' . $user->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('users.index')->with('error', 'Houve um problema ao atualizar os dados do utilizador com id ' . $user->id . '. Tente novamente.');
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
    
            return redirect()->route('users.index')->with('message', 'Utilizador com ' . $id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('users.index')->with('error', 'Houve um  problema ao eliminar os dados do utilizador com id ' . $id . '. Tente novamente.');
        }
    }
}
