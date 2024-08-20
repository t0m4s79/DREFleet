<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()//: Response
    {
        // $users = User::leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
        //         ->whereNull('drivers.user_id')
        //         ->select('users.*')
        //         ->get();

        // $drivers = Driver::join('users', 'users.id', '=', 'drivers.user_id')
        //         ->select('drivers.*', 'users.*')
        //         ->get();

        $drivers = Driver::all();

        return Inertia::render('Drivers/AllDrivers',['drivers' => $drivers]);
    }

    public function createDriver(Request $request) {
        $incomingFields = $request->validate([
            'user_id' => ['required', 'unique:drivers,user_id'],
            'heavy_license' => 'required'          
        ]);

        $incomingFields['user_id'] = strip_tags($incomingFields['user_id']);
        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        
        Driver::create($incomingFields);
        return redirect('/drivers');
    }

    public function showEditScreen(Driver $driver): Response
    {
        return Inertia::render('Drivers/Edit', [
            'driver' => $driver,
        ]);
    }

    public function editDriver(Driver $driver, Request $request) {
        $incomingFields = $request->validate([
            'user_id' => 'required',
            'heavy_license' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'status' => 'required',
        ]);

        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);
        
        $driver->update([
            'heavy_license' => $incomingFields['heavy_license'],
        ]);
    
        $user = User::findOrFail($incomingFields['user_id']);
        $user->update([
            'name' => $incomingFields['name'],
            'email' => $incomingFields['email'],
            'phone' => $incomingFields['phone'],
            'status_code' => $incomingFields['status'],
        ]);

        return redirect('/drivers');
    }

    public function update(Driver $driver, Request $request): void
    {
        //
    }
}
