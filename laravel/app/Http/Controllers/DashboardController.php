<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Models\User;
use App\Models\VehicleMaintenanceReport;
use App\Models\VehicleRefuelRequest;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Vehicle;
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

        $user = auth()->user();

        $ordersQuery = Order::where('expected_end_date', '>', now());

        // If the user is a technician or a driver, only display the orders assigned to them.
        // If the user is neither an admin nor a manager, they will not have access to any orders.

        if ($user->user_type === Roles::TECHNICIAN->value) {
            $ordersQuery->where('technician_id', $user->id);

        } elseif ($user->user_type === Roles::DRIVER->value) {
            $ordersQuery->where('driver_id', $user->id);
            
        } elseif (!in_array($user->user_type, [Roles::ADMIN->value, Roles::MANAGER->value])) {
            $ordersQuery = null; // Prevents access for unauthorized users
        }

        $orders = $ordersQuery ? $ordersQuery->get() : collect();
        

        $drivers = User::where('user_type', 'Condutor')->whereNot('status','Escondido')->whereNot('status','Inoperável')->with('driver')->get();
        $technicians = User::where('user_type', 'Técnico')->whereNot('status','Escondido')->whereNot('status','Inoperável')->get();
        $vehicles = Vehicle::whereNot('status','Escondido')->whereNot('status','Inoperável')->with(['documents', 'accessories'])->get();

        $refuelRequests = VehicleRefuelRequest::all();
        $maintenanceRequests = VehicleMaintenanceReport::all();

        $orders = $orders->map(function ($order) {
            return [
                ...$order->toArray(),
                'expected_begin_date' => Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i'),
                'expected_end_date' => Carbon::parse($order->expected_end_date)->format('d-m-Y H:i'),
            ];
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
            'refuelRequests' => $refuelRequests,
            'maintenanceRequests' => $maintenanceRequests
        ]);
    }
}
