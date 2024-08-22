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

    public function showCreateVehicleForm() {
        return Inertia::render('Vehicles/NewVehicle');
    }

    //TODO: more verification in each field and frontend verification messages!!!
    public function createVehicle(Request $request) {
        $incomingFields = $request->validate([
            'make' => 'required', 
            'model' => 'required',
            'license_plate' => 'required',
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required',
            'fuel_consumption' => 'required',
            'status' => 'required',
            'current_month_fuel_requests' => 'required'
        ]);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strip_tags($incomingFields['license_plate']);
        $incomingFields['heavy_vehicle'] = strip_tags($incomingFields['heavy_vehicle']);
        $incomingFields['wheelchair_adapted'] = strip_tags($incomingFields['wheelchair_adapted']);
        $incomingFields['capacity'] = strip_tags($incomingFields['capacity']);
        $incomingFields['fuel_consumption'] = strip_tags($incomingFields['fuel_consumption']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);
        $incomingFields['current_month_fuel_requests'] = strip_tags($incomingFields['current_month_fuel_requests']);

        Vehicle::create($incomingFields);
        return redirect('/vehicles');
    }

    public function showEditScreen(Vehicle $vehicle) {
        return Inertia::render('Vehicles/Edit',['vehicle'=> $vehicle]);
    }

    public function editVehicle(Vehicle $vehicle, Request $request) {
        $incomingFields = $request->validate([
            'make' => 'required', 
            'model' => 'required',
            'license_plate' => 'required',
            'heavy_vehicle' => 'required',
            'wheelchair_adapted' => 'required',
            'capacity' => 'required',
            'fuel_consumption' => 'required',
            'status' => 'required',
            'current_month_fuel_requests' => 'required'
        ]);

        $incomingFields['make'] = strip_tags($incomingFields['make']);
        $incomingFields['model'] = strip_tags($incomingFields['model']);
        $incomingFields['license_plate'] = strip_tags($incomingFields['license_plate']);
        $incomingFields['heavy_vehicle'] = strip_tags($incomingFields['heavy_vehicle']);
        $incomingFields['wheelchair_adapted'] = strip_tags($incomingFields['wheelchair_adapted']);
        $incomingFields['capacity'] = strip_tags($incomingFields['capacity']);
        $incomingFields['fuel_consumption'] = strip_tags($incomingFields['fuel_consumption']);
        $incomingFields['status'] = strip_tags($incomingFields['status']);
        $incomingFields['current_month_fuel_requests'] = strip_tags($incomingFields['current_month_fuel_requests']);

        $vehicle->update($incomingFields);
        return redirect('/vehicles');
    }

    public function deleteVehicle($id) {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        
        return redirect('/vehicles');
    }
}
