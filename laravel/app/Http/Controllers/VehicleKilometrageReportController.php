<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Helpers\ErrorMessagesHelper;
use App\Models\VehicleKilometrageReport;

class VehicleKilometrageReportController extends Controller
{
    public function showCreateVehicleKilometrageReportForm()
    {
        $vehicles = Vehicle::all();

        return Inertia::render('VehicleKilometrageReports/NewVehicleKilometrageReports', [
            'vehicles' => $vehicles,
        ]);
    }

    public function createVehicleKilometrageReport(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'date' => ['required', 'date'],
            'begin_kilometrage' => ['required', 'integer', 'min:0'],
            'end_kilometrage' => ['required', 'integer', 'min:0'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,user_id'],
        ], $customErrorMessages);
        
        try {
            $report = VehicleKilometrageReport::create($incomingFields);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('message', 'Relatório de kilometragem diário com id ' . $report->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o relatório de kilometragem diário para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleKilometrageReportForm(VehicleKilometrageReport $vehicleKilometrageReport)
    {
        $vehicles = Vehicle::all();

        return Inertia::render('VehicleKilometrageReports/EditVehicleKilometrageReports', [
            'report' => $vehicleKilometrageReport,
            'vehicles' => $vehicles,
        ]);
    }

    public function editVehicleKilometrageReport(VehicleKilometrageReport $vehicleKilometrageReport, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'date' => ['required', 'date'],
            'begin_kilometrage' => ['required', 'integer', 'min:0'],
            'end_kilometrage' => ['required', 'integer', 'min:0'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,user_id'],
        ], $customErrorMessages);

        try {
            $vehicleKilometrageReport->update($incomingFields);

            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('message', 'Dados do relatório de kilometragem diário com id ' . $vehicleKilometrageReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.kilometrageReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o relatório de kilometragem diário com id ' . $vehicleKilometrageReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleKilometrageReport($id)
    {
        try {
            $report = VehicleKilometrageReport::findOrFail($id);
            $report->delete();
    
            return redirect()->route('vehicles.kilometrageReports', $id)->with('message', 'Relatório de kilometragem diário com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('vehicles.kilometrageReports', $id)->with('error', 'Houve um problema ao apagar o relatório de kilometragem diário com id ' . $id . '. Tente novamente.');
        }
    }
}
