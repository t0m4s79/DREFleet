<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        ]);
    }
}
