<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    //

    public function index()
    {
        $vehicles = Vehicle::All();

        return Inertia::render('Vehicles/AllVehicles',['vehicles'=> $vehicles]);
    }
}
