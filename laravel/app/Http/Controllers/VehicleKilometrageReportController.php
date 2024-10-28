<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Models\VehicleKilometrageReport;

class VehicleKilometrageReportController extends Controller
{
    public function showCreateVehicleKilometrageReportForm()
    {
        Log::channel('user')->info('User accessed vehicle kilometrage report entry creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        return Inertia::render('VehicleKilometrageReports/NewVehicleKilometrageReport', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    public function createVehicleKilometrageReport(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'date' => ['required', 'date'],
            'begin_kilometrage' => ['required', 'integer', 'min:0'],
            'end_kilometrage' => ['required', 'integer', 'min:0', 'gte:begin_kilometrage'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,user_id'],
        ], $customErrorMessages);
        
        try {
            $report = VehicleKilometrageReport::create($incomingFields);

            Log::channel('user')->info('User created a vehicle kilometrage report entry', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kilometrage_report_id' => $report->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('message', 'Registo de kilometragem diário com id ' . $report->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle kilometrage entry', [
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o relatório de kilometragem diário para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleKilometrageReportForm(VehicleKilometrageReport $vehicleKilometrageReport)
    {
        Log::channel('user')->info('User accessed vehicle kilometrage report entry edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'kilometrage_report_id' => $vehicleKilometrageReport->id,
        ]);

        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        return Inertia::render('VehicleKilometrageReports/EditVehicleKilometrageReport', [
            'report' => $vehicleKilometrageReport,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    public function editVehicleKilometrageReport(VehicleKilometrageReport $vehicleKilometrageReport, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'date' => ['required', 'date'],
            'begin_kilometrage' => ['required', 'integer', 'min:0'],
            'end_kilometrage' => ['required', 'integer', 'min:0', 'gte:begin_kilometrage'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,user_id'],
        ], $customErrorMessages);

        try {
            $vehicleKilometrageReport->update($incomingFields);

            Log::channel('user')->info('User edited a vehicle kilometrage report entry', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kilometrage_report_id' => $vehicleKilometrageReport->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('message', 'Dados do relatório de kilometragem diário com id ' . $vehicleKilometrageReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle kilometrage entry', [
                'entry_id' => $vehicleKilometrageReport->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o relatório de kilometragem diário com id ' . $vehicleKilometrageReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleKilometrageReport($id)
    {
        try {
            $report = VehicleKilometrageReport::findOrFail($id);
            $vehicleId = $report->vehicle->id;
            $report->delete();

            Log::channel('user')->info('User deleted a vehicle kilometrage report entry', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'kilometrage_report_id' => $id,
                'vehicle_id' => $vehicleId,
            ]);
    
            return redirect()->route('vehicles.kilometrageReports', $vehicleId)->with('message', 'Registo de kilometragem diário com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle kilometrage entry', [
                'entry_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o relatório de kilometragem diário com id ' . $id . '. Tente novamente.');
        }
    }
}
