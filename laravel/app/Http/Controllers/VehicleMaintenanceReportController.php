<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Models\VehicleMaintenanceReport;

class VehicleMaintenanceReportController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed maintenance reports page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $reports = VehicleMaintenanceReport::All();

        $reports->each(function ($report) {
            $report->begin_date = Carbon::parse($report->begin_date)->format('d-m-Y');
            $report->end_date = Carbon::parse($report->end_date)->format('d-m-Y');
        });

        return Inertia::render('VehicleMaintenanceReports/AllVehicleMaintenanceReport', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'reports' => $reports,
        ]);
    }

    public function showCreateVehicleMaintenanceReportForm()
    {
        Log::channel('user')->info('User accessed vehicle maintenance report creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $vehicles = Vehicle::all();

        return Inertia::render('VehicleMaintenanceReports/NewVehicleMaintenanceReport', [
            'vehicles' => $vehicles,
        ]);
    }

    public function createVehicleMaintenanceReport(Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'begin_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:begin_date'],
            'type' => ['required', Rule::in(['Manutenção', 'Anomalia', 'Reparação', 'Outros'])],
            'description' => ['required', 'string', 'max:500'],
            'kilometrage' => ['nullable', 'integer', 'min:0'],
            'total_cost' => ['nullable', 'decimal:0,2', 'min:0'],
            'items_cost' => ['nullable', 'array'],
            'service_provider' => ['nullable', 'string'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);
        
        try {
            $incomingFields['description'] = strip_tags($incomingFields['description']);
            $incomingFields['service_provider'] = strip_tags($incomingFields['service_provider']);

            // Ensure $incomingFields['items_cost'] is an array
            $formattedData = isset($incomingFields['items_cost']) ? $incomingFields['items_cost'] : [];

            // Iterate through each key-value pair in the additional data
            foreach ($formattedData as $key => $value) {
                $formattedData[strip_tags($key)] = strip_tags($value);
            }

            if ($incomingFields['begin_date'] > now()) {
                $status = 'Agendado';

            } else if (isset($incomingFields['end_date']) && $incomingFields['end_date'] > now()) {
                $status = 'Finalizado';

            } else {
                $status = 'A decorrer';
            }

            $report = VehicleMaintenanceReport::create([
                'begin_date' => $incomingFields['begin_date'],
                'end_date' => $incomingFields['end_date'] ?? null,
                'type' => $incomingFields['type'],
                'description' => $incomingFields['description'],
                'kilometrage' => $incomingFields['kilometrage'] ?? null,
                'total_cost' => $incomingFields['total_cost'] ?? null,
                'items_cost' => $formattedData != [] ? $formattedData : null,
                'service_provider' => $incomingFields['service_provider'] ?? null,
                'status' => $status,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            Log::channel('user')->info('User created a vehicle maintenance report', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'maintenance_report_id' => $report->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->route('vehicles.maintenanceReports', $incomingFields['vehicle_id'])->with('message', 'Relatório de manutenção com id ' . $report->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' criado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error creating vehicle maintenance report', [
                'vehicle_id' => $incomingFields['vehicle_id'] ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.maintenanceReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao criar o relatório de manutenção para o veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function showEditVehicleMaintenanceReportForm(VehicleMaintenanceReport $vehicleMaintenanceReport)
    {
        Log::channel('user')->info('User accessed vehicle maintenance report edit page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'maintenance_report_id' => $vehicleMaintenanceReport->id,
        ]);

        $vehicles = Vehicle::all();

        $vehicleMaintenanceReport->each(function ($report) {
            $report->begin_date = Carbon::parse($report->begin_date)->format('d-m-Y');
            $report->end_date = Carbon::parse($report->begin_date)->format('d-m-Y');
        });

        return Inertia::render('VehicleMaintenanceReports/EditVehicleMaintenanceReport', [
            'report' => $vehicleMaintenanceReport,
            'vehicles' => $vehicles,
        ]);
    }

    public function editVehicleMaintenanceReport(VehicleMaintenanceReport $vehicleMaintenanceReport, Request $request)
    {
        // Load custom error messages from helper
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'begin_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:begin_date'],
            'type' => ['required', Rule::in(['Manutenção', 'Anomalia', 'Reparação', 'Outros'])],
            'description' => ['required', 'string', 'max:500'],
            'kilometrage' => ['nullable', 'integer', 'min:0'],
            'total_cost' => ['nullable', 'decimal:0,2', 'min:0'],
            'items_cost' => ['nullable', 'array'],
            'service_provider' => ['nullable', 'string', 'max:100'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
        ], $customErrorMessages);

        try {
            // Ensure $incomingFields['items_cost'] is an array
            $formattedData = isset($incomingFields['items_cost']) ? $incomingFields['items_cost'] : [];

            // Iterate through each key-value pair in the additional data
            foreach ($formattedData as $key => $value) {
                $formattedData[strip_tags($key)] = strip_tags($value);
            }

            if ($incomingFields['begin_date'] > now()) {
                $status = 'Agendado';

            } else if (isset($incomingFields['end_date']) && $incomingFields['end_date'] > now()) {
                $status = 'Finalizado';

            } else {
                $status = 'A decorrer';
            }

            $vehicleMaintenanceReport->update([
                'begin_date' => $incomingFields['begin_date'],
                'end_date' => $incomingFields['end_date'] ?? null,
                'type' => $incomingFields['type'],
                'description' => $incomingFields['description'],
                'kilometrage' => $incomingFields['kilometrage'] ?? null,
                'total_cost' => $incomingFields['total_cost'] ?? null,
                'items_cost' => $formattedData != [] ? $formattedData : null,
                'service_provider' => $incomingFields['service_provider'] ?? null,
                'status' => $status,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            Log::channel('user')->info('User edited a vehicle maintenance report', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'maintenance_report_id' => $vehicleMaintenanceReport->id,
                'vehicle_id' => $incomingFields['vehicle_id'],
            ]);

            return redirect()->route('vehicles.maintenanceReports', $incomingFields['vehicle_id'])->with('message', 'Dados do relatório de manutenção com id ' . $vehicleMaintenanceReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error editing vehicle maintenance report', [
                'report_id' => $vehicleMaintenanceReport->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('vehicles.maintenanceReports', $incomingFields['vehicle_id'])->with('error', 'Houve um problema ao atualizar o relatório de manutenção com id ' . $vehicleMaintenanceReport->id . ' pertencente ao veículo com id ' . $incomingFields['vehicle_id'] . '. Tente novamente.');
        }
    }

    public function deleteVehicleMaintenanceReport($id)
    {
        try {
            $report = VehicleMaintenanceReport::findOrFail($id);
            $vehicleId = $report->vehicle->id;
            $report->delete();

            Log::channel('user')->info('User deleted a vehicle maintenance report', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'maintenance_report_id' => $id,
                'vehicle_id' => $vehicleId,
            ]);
    
            return redirect()->route('vehicles.maintenanceReports', $vehicleId)->with('message', 'Relatório de manutenção com id ' . $id . ' eliminado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting vehicle maintenance report', [
                'report_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('vehicles.index')->with('error', 'Houve um problema ao apagar o relatório de manutenção com id ' . $id . '. Tente novamente.');
        }
    }
}
