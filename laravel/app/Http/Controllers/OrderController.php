<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\Point;

class OrderController extends Controller
{
    public function index()
    {
        //return Inertia::render('Orders/Index');
    }

    public function showCreateOrderForm()           //TODO: INCLUDE TECHNICIAN
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();        ;
        $managers = User::where('user_type', 'Gestor')->get();        ;

        return Inertia::render('Orders/NewOrder', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'technicians' => $technicians,
            'managers' => $managers,
        ]);
    }

    //TODO: CHECK IF "date" VALIDATION IS THE BEST SOLUTION
    //TODO: TRAJECTORY VALIDATION
    //TODO: CHECK IF TECHNICIAN IS IN FACT TECHNICIAN AND NOT ANOTHER TYPE OF USER
    //TODO: CHECK IF ADDRESSES ARE VALID/EXIST
    //TODO: CAN DRIVER/VEHICLE BE NULL??
    //TODO: CHECK IF RELATIONS IN USER ARE CORRECT
    //TODO: CREATE THE 'ORDERS' PAGE
    //TODO: UNIT TESTS!!!
    public function createOrder(Request $request)
    {
        $incomingFields = $request->validate([
            'begin_address' => 'required|string|max:255',
            'end_address' => 'required|string|max:255',     
            'begin_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'begin_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'begin_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'trajectory' => ['required', 'json'],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['required','exists:drivers,user_id'],
            'technician_id' => ['required','exists:users,id'],
        ]);

        $incomingFields['begin_address'] = strip_tags($incomingFields['begin_address']);
        $incomingFields['end_address'] = strip_tags($incomingFields['end_address']);
        $incomingFields['begin_date'] = strip_tags($incomingFields['begin_date']);
        $incomingFields['end_date'] = strip_tags($incomingFields['end_date']);
        $incomingFields['begin_latitude'] = strip_tags($incomingFields['begin_latitude']);
        $incomingFields['begin_longitude'] = strip_tags($incomingFields['begin_longitude']);
        $incomingFields['end_latitude'] = strip_tags($incomingFields['end_latitude']);
        $incomingFields['end_longitude'] = strip_tags($incomingFields['end_longitude']);
        $incomingFields['trajectory'] = strip_tags($incomingFields['trajectory']);
        $incomingFields['vehicle_id'] = strip_tags($incomingFields['vehicle_id']);
        $incomingFields['driver_id'] = strip_tags($incomingFields['driver_id']);
        $incomingFields['technician_id'] = strip_tags($incomingFields['technician_id']);

        try {
            $beginCoordinates = new Point($incomingFields['begin_latitude'], $incomingFields['begin_longitude']);
            $endCoordinates = new Point($incomingFields['end_latitude'], $incomingFields['end_longitude']);

            Order::create([
                'begin_address' => $incomingFields['begin_address'],
                'end_address' => $incomingFields['end_address'],
                'begin_date' => $incomingFields['begin_date'],
                'end_date' => $incomingFields['end_date'],
                'begin_coordinates' => $beginCoordinates,
                'end_coordinates' => $endCoordinates,
                'trajectory' => $incomingFields['trajectory'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
            ]);

            return redirect('/orders')->with('message', 'Pedido criado com sucesso!');;

        } catch (\Exception $e) {
            return redirect('/orders')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    public function showEditOrderForm()
    {

    }

    public function editOrder()
    {

    }

    public function deleteOrder()
    {

    }

    public function customErrorMessages() {
        $customErrorMessages = [

        ];

        return $customErrorMessages;
    }
}
