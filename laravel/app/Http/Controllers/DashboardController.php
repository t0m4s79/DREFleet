<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $drivers = User::where('user_type', 'Condutor')->whereNot('status','Escondido')->whereNot('status','Indisponível')->get();
        $technicians = User::where('user_type', 'Técnico')->whereNot('status','Escondido')->whereNot('status','Indisponível')->get();
        $vehicles = Vehicle::whereNot('status','Escondido')->whereNot('status','Indisponível');

        return Inertia::render('Dashboard', [
            // 'flash' => [
            //     'message' => session('message'),
            //     'error' => session('error'),
            // ],
            'drivers' => $drivers,
            'technicians' => $technicians,
            'vehicles' => $vehicles,
        ]);
    }
}
