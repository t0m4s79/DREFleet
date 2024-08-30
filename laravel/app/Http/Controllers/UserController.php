<?php

namespace App\Http\Controllers;

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

        return Inertia::render('Users/AllUsers', ['users' => $users]);
    }

    public function showCreateUserForm()
    {
        return Inertia::render('Users/NewUser');
    }

    public function createUser(Request $request)
    {
        $customErrorMessages = [
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O campo nome não pode mais de 255 carateres',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O campo e-mail deve ser um endereço de e-mail válido.',
            'email.unique' => 'Este endereço de e-mail já está em uso.',
            'phone.required' => 'O campo telefone é obrigatório.',
            'phone.numeric' => 'O campo telefone deve conter apenas números.',
            'phone.digits_between' => 'O campo telefone deve ter entre 9 e 15 dígitos.',
            'phone.unique' => 'Este número de telefone já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.mixed_case' => 'A senha deve conter pelo menos uma letra maiúscula e uma letra minúscula.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um caracter especial.',
            'password.confirmed' => 'As senhas não coincidem.',
        ];
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
                'status_code' => '0',
                'password' => Hash::make($request->password),
            ]);

            return redirect('/users')->with('message', 'Utilizador criada com sucesso!');;
        } catch (\Exception $e) {
            return redirect('users')->with('error', 'Houve um problema ao criar o utilizador. Tente novamente.');
        }
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

    public function showEditScreen(User $user)
    {
        return Inertia::render('Users/Edit', ['user' => $user]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/users');
    }
}
