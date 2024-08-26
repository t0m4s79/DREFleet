<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rules;
use Inertia\Response;

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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'user_type' => 'Nenhum',
            'status_code' => '0',
            'password' => Hash::make($request->password),
        ]);

        return redirect('/users');
    }
}
