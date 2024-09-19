<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kid;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Carbon\Traits\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use MatanYadaev\EloquentSpatial\Objects\Point;

//TODO: ARE MANAGERS NEEDED IN EDIT/NEW?
class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();

        return Inertia::render('Orders/AllOrders',[
           'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'orders' => $orders,
        ]);
    }

    //TODO: ADD PLACES ATTACHED TO THE KIDS ARRAY
    public function showCreateOrderForm()           //TODO: INCLUDE TECHNICIAN
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::all();

        return Inertia::render('Orders/NewOrder', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'technicians' => $technicians,
            'managers' => $managers,
            'kids' => $kids,
        ]);
    }

    //TODO: CHECK IF TECHNICIAN IS IN FACT TECHNICIAN AND NOT ANOTHER TYPE OF USER
    //TODO: CHECK IF ADDRESSES ARE VALID/EXIST
    //TODO: CAN DRIVER/VEHICLE BE NULL??
    //TODO: UNIT TESTS!!!
    //TODO: FRONTEND BACKEND-> STYLE AND VERIFICATION
    //TODO: CUSTOM ERROR MESSAGES
    //TODO: SNACKBAR
    //TODO: HEAVY LICENSE FOR HEAVY VEHICLE,....
    public function createOrder(Request $request)
    {
        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'begin_address' => 'required|string|max:255',
            'begin_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'begin_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_address' => 'required|string|max:255',
            'end_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],   
            'begin_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
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
        $incomingFields['trajectory'] = json_encode($incomingFields['trajectory']);
        $incomingFields['vehicle_id'] = strip_tags($incomingFields['vehicle_id']);
        $incomingFields['driver_id'] = strip_tags($incomingFields['driver_id']);
        $incomingFields['technician_id'] = strip_tags($incomingFields['technician_id']);

        $beginCoordinates = new Point($incomingFields['begin_latitude'], $incomingFields['begin_longitude']);            $endCoordinates = new Point($incomingFields['end_latitude'], $incomingFields['end_longitude']);
        $endCoordinates = new Point($incomingFields['end_latitude'], $incomingFields['end_longitude']);

        try {
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
            Log::error($e);  // Log the error for debugging
            return redirect('/orders/create')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    //TODO: ADD PLACES ATTACHED TO THE KIDS ARRAY
    public function showEditOrderForm(Order $order)
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::all();

        return Inertia::render('Orders/EditOrder', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'order' => $order,
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'technicians' => $technicians,
            'managers' => $managers,
            'kids' => $kids,
        ]);
    }

    //TODO: IF EDITED AFTER APPROVED NEEDS REAPPROVAL
    public function editOrder(Order $order, Request $request)
    {
        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'begin_address' => 'required|string|max:255',
            'begin_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'begin_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_address' => 'required|string|max:255',
            'end_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,2}\.\d{1,6}$/'],   
            'begin_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
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
        $incomingFields['trajectory'] = json_encode($incomingFields['trajectory']);
        $incomingFields['vehicle_id'] = strip_tags($incomingFields['vehicle_id']);
        $incomingFields['driver_id'] = strip_tags($incomingFields['driver_id']);
        $incomingFields['technician_id'] = strip_tags($incomingFields['technician_id']);

        try {
            $beginCoordinates = new Point($incomingFields['begin_latitude'], $incomingFields['begin_longitude']);
            $endCoordinates = new Point($incomingFields['end_latitude'], $incomingFields['end_longitude']);

            $order->update([
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

    public function deleteOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return redirect('/orders')->with('message', 'Pedido apagado com sucesso!');

        } catch (\Exception $e) {
            return redirect('/technicians')->with('error', 'Houve um problema ao apagar o pedido. Tente novamente.');
        }
    }

    public function approveOrder(Order $order, Request $request) {
        //$managerId = Auth::id(); --------> to use on calling this page to get logged in user id

        $incomingFields = $request->validate([
            'manager_id' => ['required', 'exists:users,id']
        ]);

        $incomingFields['manager_id'] = strip_tags($incomingFields['manager_id']);

        try {
            $currentDate = Date::now()->toDateTimeString();
            $order->update([
                'manager_id' => $incomingFields['manager_id'],
                'approved_date' => $currentDate,
            ]);

            return redirect('/orders')->with('message', 'Dados do pedido atualizados com sucesso!');

        } catch (\Exception $e) {
            return redirect('/orders')->with('error', 'Houve um problema ao editar os dados do pedido. Tente novamente.');
        }
    }

}
