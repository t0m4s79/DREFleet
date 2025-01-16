<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\RoleUserTypeValidation;
use App\Rules\DriverLicenseNumberValidation;

class DriverController extends Controller
{
    public function index() : Response
    {
        Log::channel('user')->info('User accessed drivers page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $drivers = Driver::all();

        $drivers->each(function ($driver) {
            $driver->license_expiration_date = Carbon::parse($driver->license_expiration_date)->format('d-m-Y');
            $driver->heavy_license_type = $driver->heavy_license_type ?? '-';
            $driver->phone = $driver->phone ?? '-';
            //$driver->tcc = $driver->tcc == 1 ? 'Sim' : 'Não';
            $driver->tcc_expiration_date =  $driver->tcc_expiration_date != null ? Carbon::parse($driver->tcc_expiration_date)->format('d-m-Y') : '-';
        });

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
        Log::channel('user')->info('User accessed drivers creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $users = User::where('user_type', 'Nenhum')->get();

        return Inertia::render('Drivers/NewDriver', ['users' => $users]);
    }

    public function createDriver(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'user_id' => [
                'required', 
                'numeric',
                new RoleUserTypeValidation(),
            ],
            'license_number' => [
                'required', 
                'regex:/^[A-Z]{1,2}-\d{6} \d$/',
                new DriverLicenseNumberValidation($request->input('user_id')),
            ],
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'license_expiration_date' => ['required', 'date'],
            'tcc' => ['required', 'boolean'],
            'tcc_expiration_date' => ['nullable' , 'date', 'required_if:tcc,1'],
        ], $customErrorMessages);

        $incomingFields['license_number'] = strip_tags($incomingFields['license_number']);

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        } 

        DB::beginTransaction();
        try {
            $driver = Driver::create([
                'user_id' => $incomingFields['user_id'],
                'license_number' => $incomingFields['license_number'],
                'heavy_license' => $incomingFields['heavy_license'],
                'heavy_license_type' => $incomingFields['heavy_license_type'],
                'license_expiration_date' => $incomingFields['license_expiration_date'],
                'tcc' => $incomingFields['tcc'],
                'tcc_expiration_date' => $incomingFields['tcc_expiration_date'],
            ]);

            $user = User::findOrFail($incomingFields['user_id']);
            $user->update([
                'user_type' => "Condutor",
            ]);
            
            DB::commit();

            Log::channel('user')->info('User created driver', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'driver_id' => $incomingFields['user_id'] ?? null,
            ]);

            return redirect()->route('drivers.index')->with('message', 'Condutor/a com id ' . $driver->user_id . ' criado/a com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the exception with a custom message and context
            Log::channel('usererror')->error('Error creating driver', [
                'user_id' => $incomingFields['user_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao adicionar o utilizador com id ' . $incomingFields['user_id'] . ' à lista de condutores. Tente novamente.');
        }
    }

    public function showEditDriverForm(Driver $driver): Response
    {
        Log::channel('user')->info('User accessed drivers edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'driver_id' => $driver->user_id ?? null,
        ]);

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
            'license_number' => [
                'required', 
                'regex:/^[A-Z]{1,2}-\d{6} \d$/',
                new DriverLicenseNumberValidation($request->input('user_id')),
            ],
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'license_expiration_date' => ['required', 'date'],
            'tcc' => ['required', 'boolean'],
            'tcc_expiration_date' => ['nullable' , 'date', 'required_if:tcc,1'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);

        Log::channel('user')->info('User tried editing a driver', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'driver_id' => $incomingFields['user_id'] ?? null,
        ]);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['license_number'] = strip_tags($incomingFields['license_number']);

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        }
        
        DB::beginTransaction();
        try {
            $driver->update([
                'license_number' => $incomingFields['license_number'],
                'heavy_license' => $incomingFields['heavy_license'],
                'heavy_license_type' => $incomingFields['heavy_license_type'],
                'license_expiration_date' => $incomingFields['license_expiration_date'],
                'tcc' => $incomingFields['tcc'],
                'tcc_expiration_date' => $incomingFields['tcc_expiration_date'],
            ]);

            $user = User::findOrFail($incomingFields['user_id']);
            $user->update([
                'name' => $incomingFields['name'],
                'email' => $incomingFields['email'],
                'phone' => $incomingFields['phone'],
                'status' => $incomingFields['status'],
            ]);

            DB::commit();

            Log::channel('user')->info('User edited a driver ', [
                'auth_user_id' => $this->loggedInUserId ?? null,
            ]);

            return redirect()->route('drivers.index')->with('message', 'Dados do/a Condutor/a com id ' . $driver->user_id . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the exception with a custom message and context
            Log::channel('usererror')->error('Error editing driver', [
                'user_id' => $incomingFields['user_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao atualizar os dados do/a condutor/a com id ' . $driver->user_id . '. Tente novamente.');
        } 
    }

    public function deleteDriver($id)
    {
        DB::beginTransaction();
        try {
            $driver = Driver::findOrFail($id);
            $driver->delete();

            $user = User::findOrFail($id);
            $user->update([
                'user_type' => "Nenhum",
            ]);

            DB::commit();

            Log::channel('user')->info('User deleted a driver', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'driver_id' => $id ?? null,
            ]);

            return redirect()->route('drivers.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de condutores com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('usererror')->error('Error deleting driver', [
                'user_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de condutores. Tente novamente.');
        }
    }
}
