<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        Log::channel('user')->info('User accessed dashboard page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $drivers = User::where('user_type', 'Condutor')->whereNot('status','Escondido')->whereNot('status','Indisponível')->with('driver')->get();
        $technicians = User::where('user_type', 'Técnico')->whereNot('status','Escondido')->whereNot('status','Indisponível')->get();
        $vehicles = Vehicle::whereNot('status','Escondido')->whereNot('status','Indisponível')->get();
        $orders = Order::where('expected_end_date', '>', now())->get();

        $orders->each(function ($order) {
            // Format the dates as dd-mm-yyyy
            $order->expected_begin_date = Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i');
            $order->expected_end_date = Carbon::parse($order->expected_end_date)->format('d-m-Y H:i');
        });

        return Inertia::render('Dashboard', [
            // 'flash' => [
            //     'message' => session('message'),
            //     'error' => session('error'),
            // ],
            'drivers' => $drivers,
            'technicians' => $technicians,
            'vehicles' => $vehicles,
            'orders' => $orders,
            'stats' => [
                'total_vehicles' => Vehicle::count(),
                'total_drivers' => User::where('user_type', 'Condutor')->count(),
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'Por Aprovar')->count(),
                'ongoing_orders' => Order::where('status', 'Em Curso')->count(),
                'pending_maintenance' => VehicleMaintenanceReport::where('status', 'A decorrer')->count(),
            ],
            'latest_orders' => Order::latest()->take(5)->with(['vehicle', 'driver'])->get(),
            'latest_maintenance_reports' => VehicleMaintenanceReport::latest()->take(5)->with('vehicle')->get(),
            'order_status_chart' => Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->get(),
        ]);
    }
}
