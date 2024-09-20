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
        $users = User::leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
            ->whereNull('drivers.user_id')
            ->select('users.*')
            ->get();

        return Inertia::render('Drivers/NewDriver', ['users' => $users]);
    }

    public function createDriver(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'user_id' => ['required', 'unique:drivers,user_id'],
            'heavy_license' => 'required'
        ], $customErrorMessages);

        $incomingFields['user_id'] = strip_tags($incomingFields['user_id']);
        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);

        try {
            $user = User::findOrFail($incomingFields['user_id']);

            if ($user->user_type != 'Nenhum') {
                return redirect('/drivers')->with('error', 'Somente utilizadores de tipo "Nenhum" podem ser convertidos em condutores.');
            }

            $driver = Driver::create($incomingFields);
            $user->update([
                'user_type' => "Condutor",
            ]);
            return redirect()->route('drivers.index')->with('message', 'Condutor/a com id ' . $driver->user_id . ' criado/a com sucesso!');
        } catch (\Exception $e) {
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
            'heavy_license' => 'required',
            'name' => 'required|string|max:255',
            'email' => ['required', 'email'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => 'required',
        ], $customErrorMessages);

        $incomingFields['heavy_license'] = strip_tags($incomingFields['heavy_license']);
        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['phone'] = strip_tags($incomingFields['phone']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);

        // if ($incomingFields['heavy_license'] == 'Sim') {              //These if's can be taken out if heavy attribute method is taken out of the driver model
        //     $incomingFields['heavy_license'] = '1';                 //but the the table will show heavy license as 0 or 1 instead of Sim ou Não
        // } else if ($incomingFields['heavy_license'] == 'Não') {
        //     $incomingFields['heavy_license'] = '0';
        // }

        try {
            $driver->update([
                'heavy_license' => $incomingFields['heavy_license'],
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
            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de condutores. Tente novamente.');
        }
    }
}
