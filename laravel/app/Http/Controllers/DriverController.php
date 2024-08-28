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

        return Inertia::render('Drivers/AllDrivers',[
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers]);
    }

    public function showCreateDriverForm()
    {
        $users = User::leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
                ->whereNull('drivers.user_id')
                ->select('users.*')
                ->get();
                
        return Inertia::render('Drivers/NewDriver', ['users' => $users]);
    }

    public function createDriver(Request $request) {
        $incomingFields = $request->validate([
            'user_id' => ['required', 'unique:drivers,user_id'],
            'heavy_license' => 'required'          
        ]);

        $incomingFields['user_id'] = strip_tags($incomingFields['user_id']);
        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        
        try{
            Driver::create($incomingFields);
            $user = User::findOrFail($incomingFields['user_id']);
            $user->update([
                'user_type' => "Condutor",
            ]);
            return redirect('/drivers')->with('message', 'Condutor/a criado/a com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Houve um problema ao criar o condutor. Tente novamente.');
        }
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
        
        try {
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

            return redirect('/drivers')->with('message', 'Dados do/a Condutor/a atualizados com sucesso!');
        }  catch (\Exception $e) {
            return redirect()->back()->with('error', 'Houve um problema ao editar os dados da criança. Tente novamente mais tarde.');
        }
    }

    public function deleteDriver($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();

        $user = User::findOrFail($id);
        $user->update([
            'user_type' => "Nenhum",
        ]);
        
        return redirect('/drivers');
    }
}
