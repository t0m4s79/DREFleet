<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\User;
use App\Models\Driver;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index() //: Response
    {
        $drivers = Driver::all();

        return Inertia::render('Drivers/AllDrivers', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers,
        ]);
    }

    public function showCreateDriverForm()
    {
        $users = User::where('user_type', 'Nenhum')->get();

        return Inertia::render('Drivers/NewDriver', ['users' => $users]);
    }

    public function createDriver(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'user_id' => ['required', 'numeric'],
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
        ], $customErrorMessages);

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        } 

        try {
            $user = User::findOrFail($incomingFields['user_id']);

            //TODO: SHOULD BE FRONT-END INSTEAD OF REDIRECT
            if ($user->user_type != 'Nenhum') {
                return redirect('/drivers')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em condutores.');
            }

            $driver = Driver::create($incomingFields);
            $user->update([
                'user_type' => "Condutor",
            ]);
            return redirect()->route('drivers.index')->with('message', 'Condutor/a com id ' . $driver->user_id . ' criado/a com sucesso!');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $user->id . ' à lista de condutores. Tente novamente.');
        }
    }

    public function showEditDriverForm(Driver $driver): Response
    {
        return Inertia::render('Drivers/EditDriver', [
            'driver' => $driver,
        ]);
    }

    public function editDriver(Driver $driver, Request $request)
    {

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'user_id' => 'required',
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        } 

        try {
            $driver->update([
                'heavy_license' => $incomingFields['heavy_license'],
                'heavy_license_type' => $incomingFields['heavy_license_type'],
            ]);

            $user = User::findOrFail($incomingFields['user_id']);
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            return redirect()->route('drivers.index')->with('message', 'Dados do/a Condutor/a com id ' . $driver->user_id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao atualizar os dados do/a condutor/a com id ' . $driver->user_id . '. Tente novamente.');
        }
    }

    public function deleteDriver($id)
    {
        try {
            $driver = Driver::findOrFail($id);
            $driver->delete();

            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            return redirect()->route('drivers.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de condutores com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de condutores. Tente novamente.');
        }
    }
}
