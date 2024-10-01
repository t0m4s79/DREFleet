<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Driver;
use Illuminate\Http\Request;
use InvalidArgumentDException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\RoleUserTypeValidation;

class DriverController extends Controller
{
    public function index() : Response
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

    //TODO: FRONT-END FOR LICENSE NUMBER
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
            /*
                Aveiro - AV.
                Beja - BE.
                Braga - BR.
                Bragança - BG.
                Castelo Branco - CB.
                Coimbra - C.
                Évora - E.
                Faro - FA.
                Guarda - GD.
                Leiria - LE.
                Lisboa - L.
                Portalegre - PT.
                Porto - P.
                Santarém - SA.
                Setúbal - SE.
                Viana do Castelo - VC.
                Vila Real - VR.
                Viseu - VS.
                Angra do Heroísmo - AN.
                Horta - H.
                Ponta Delgada - A.
                Funchal - M.
            */
            'license_region_identifier' => ['required', 'min:1', 'max:2', Rule::in(['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M'])],
            'license_middle_digits' => ['required', 'regex:/^[0-9]{6}$/'],
            'license_last_digit' => ['required', 'regex:/^[0-9]{1}$/'],
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
        ], $customErrorMessages);

        $incomingFields['license_region_identifier'] = strip_tags($incomingFields['license_region_identifier']);
        $incomingFields['license_region_identifier'] = strip_tags($incomingFields['license_region_identifier']);
        $incomingFields['license_region_identifier'] = strip_tags($incomingFields['license_region_identifier']);

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        } 

        $licenseNumber = $incomingFields['license_region_identifier'] . '-' . $incomingFields['license_middle_digits'] . ' ' .  $incomingFields['license_last_digit'];

        DB::beginTransaction();
        try {
            $user = User::findOrFail($incomingFields['user_id']);

            //TODO: SHOULD BE FRONT-END MESSAGE
            if (DB::table('drivers')->where('license_number', $licenseNumber)->exists()) {
                throw new \InvalidArgumentException("Este número de carta já está associado a outro condutor");
            }

            $driver = Driver::create([
                'user_id' => $incomingFields['user_id'],
                'license_number' => $licenseNumber,
                'heavy_license' => $incomingFields['heavy_license'],
                'heavy_license_type' => $incomingFields['heavy_license_type'],
            ]);
            $user->update([
                'user_type' => "Condutor",
            ]);
            
            DB::commit();

            return redirect()->route('drivers.index')->with('message', 'Condutor/a com id ' . $driver->user_id . ' criado/a com sucesso!');
        
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();        
            return redirect()->back()->with('error', $e->getMessage());
        
        } catch (\Exception $e) {
            DB::rollBack();
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

    //TODO: FRONT-END FOR LICENSE NUMBER
    public function editDriver(Driver $driver, Request $request)
    {

        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'user_id' => 'required',
            /*
                Aveiro - AV.
                Beja - BE.
                Braga - BR.
                Bragança - BG.
                Castelo Branco - CB.
                Coimbra - C.
                Évora - E.
                Faro - FA.
                Guarda - GD.
                Leiria - LE.
                Lisboa - L.
                Portalegre - PT.
                Porto - P.
                Santarém - SA.
                Setúbal - SE.
                Viana do Castelo - VC.
                Vila Real - VR.
                Viseu - VS.
                Angra do Heroísmo - AN.
                Horta - H.
                Ponta Delgada - A.
                Funchal - M.
            */
            'license_region_identifier' => ['required', 'min:1', 'max:2', Rule::in(['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M'])],
            'license_middle_digits' => ['required', 'regex:/^[0-9]{6}$/'],
            'license_last_digit' => ['required', 'regex:/^[0-9]{1}$/'],
            'heavy_license' => ['required', 'boolean'],
            'heavy_license_type' => ['required_if:heavy_license,1', Rule::in([null, 'Mercadorias', 'Passageiros'])], // Required only if heavy_vehicle is 1
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase'],
            'phone' => ['required', 'numeric', 'regex:/^[0-9]{9,15}$/'],
            'status' => ['required', Rule::in(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido'])],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);
        $incomingFields['email'] = strip_tags($incomingFields['email']);
        $incomingFields['license_region_identifier'] = strip_tags($incomingFields['license_region_identifier']);

        $licenseNumber = $incomingFields['license_region_identifier'] . '-' . $incomingFields['license_middle_digits'] . ' ' .  $incomingFields['license_last_digit'];

        if($incomingFields['heavy_license'] == '0') {
            $incomingFields['heavy_license_type'] = null;
        }
        
        DB::beginTransaction();
        try {
            //TODO: SHOULD BE FRONT-END MESSAGE
            if (DB::table('drivers')->where('license_number', $licenseNumber)->whereNot('user_id', $driver->user_id)->exists()) {
                throw new \InvalidArgumentException("Este número de carta já está associado a outro condutor");
            }

            $driver->update([
                'license_number' => $licenseNumber,
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

            DB::commit();

            return redirect()->route('drivers.index')->with('message', 'Dados do/a Condutor/a com id ' . $driver->user_id . ' atualizados com sucesso!');

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();        
            return redirect()->back()->with('error', $e->getMessage());
        
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
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

            return redirect()->route('drivers.index')->with('message', 'Utilizador com id ' . $id . ' retirado da lista de condutores com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('drivers.index')->with('error', 'Houve um problema ao retirar o utilizador com id ' . $id . ' da lista de condutores. Tente novamente.');
        }
    }
}
