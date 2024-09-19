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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|numeric|digits_between:9,15|unique:users,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], $customErrorMessages);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_type' => 'Nenhum',
                'status' => '0',
                'password' => Hash::make($request->password),
            ]);

            return redirect('/users')->with('message', 'Utilizador criada com sucesso!');;
        } catch (\Exception $e) {
            return redirect('users')->with('error', 'Houve um problema ao criar o utilizador. Tente novamente.');
        }
    }

    public function showEditUserForm(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
        ]);
    }

    public function editUser(User $user, Request $request)
    {        //TODO: should phone be unique for each user???
        $incomingFields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)], // Ignore current user's email,
            'phone' => 'required|numeric|digits_between:9,15|unique:users,phone',
        ]);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);

        $user->update($incomingFields);
        return redirect('/users');
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
    
            return redirect('/users')->with('message', 'Utilizador apagado com sucesso!');

        } catch (\Exception $e) {
            return redirect('/users')->with('error', 'Houve um ao apagar o utilizador. Tente novamente.');
        }
    }
}
