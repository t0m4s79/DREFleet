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

        return Inertia::render('Users/AllUsers',['users'=> $users]);
    }

    public function showCreateUserForm() {
        return Inertia::render('Users/NewUser');
    }

    public function createUser(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:15|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try{
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

    public function editUser(User $user, Request $request) {        //TODO: should phone be unique for each user???
        $incomingFields = $request->validate([
            'name' => 'required', 
            'email' => ['required','email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)], // Ignore current user's email,
            'phone' => ['required', 'min:9', 'max:15'],
        ]);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);

        $user->update($incomingFields);
        return redirect('/users');
    }

    public function showEditScreen(User $user) {
        return Inertia::render('Users/Edit',['user'=> $user]);
    }

    public function deleteUser($id) {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect('/users');
    }
}
