<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $users = User::leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
                ->whereNull('drivers.user_id')
                ->select('users.*')
                ->get();

        $drivers = Driver::join('users', 'users.id', '=', 'drivers.user_id')
                ->select('drivers.*', 'users.*')
                ->get();

        return Inertia::render('Drivers/All',['users' => $users, 'drivers' => $drivers, 'csrfToken' => csrf_token()]);
    }

    public function createDriver(Request $request) {
        $incomingFields = $request->validate([
            'user_id' => ['required', 'unique:drivers,user_id'],
            'heavy_license' => 'required'          
        ]);

        $incomingFields['user_id'] = strip_tags($incomingFields['user_id']);
        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        
        Driver::create($incomingFields);
        return redirect('/drivers')->with('message', 'Condutor criado com sucesso!');
    }
}
