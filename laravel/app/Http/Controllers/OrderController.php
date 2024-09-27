<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kid;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Place;
use App\Models\Vehicle;
use Carbon\Traits\Date;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use MatanYadaev\EloquentSpatial\Objects\Point;

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
    public function showCreateOrderForm()
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::with('places')->get();
        $places = Place::all();

        return Inertia::render('Orders/NewOrder', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'technicians' => $technicians,
            'managers' => $managers,
            'places' => $places,
            'kids' => $kids,
        ]);
    }

    //TODO: CAN DRIVER/VEHICLE BE NULL??
    //TODO: FRONTEND BACKEND-> STYLE AND VERIFICATION
    //TODO: CUSTOM ERROR MESSAGES
    //TODO: SNACKBAR
    //TODO: HEAVY LICENSE FOR HEAVY VEHICLE,....
    public function createOrder(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'begin_address' => ['required' , 'string', 'max:255'],
            'begin_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'begin_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],
            'end_address' => ['required', 'string', 'max:255'],
            'end_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],   
            'begin_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['required','exists:drivers,user_id'],
            'technician_id' => ['required','exists:users,id'],
        ], $customErrorMessages);

        $incomingFields['begin_address'] = strip_tags($incomingFields['begin_address']);
        $incomingFields['end_address'] = strip_tags($incomingFields['end_address']);

        $beginCoordinates = new Point($incomingFields['begin_latitude'], $incomingFields['begin_longitude']);
        $endCoordinates = new Point($incomingFields['end_latitude'], $incomingFields['end_longitude']);
        
        try {
            $user = User::find($request->input('technician_id'));
            if (!$user || $user->user_type !== 'Técnico') {     //TODO: ADD THIS ERRORS TO FRONT-END INSTEAD OF REDIRECTING
                throw ValidationException::withMessages([       //TODO: Check this code and unit tests
                    'O valor do campo selecionado para o técnico é inválido. Tente novamente.'
                ]);
            }

            $vehicle = Vehicle::find($request->input('vehicle_id'));
            $driver = Driver::find($request->input('driver_id'));

            if ($vehicle->heavy_vehicle == '1' && $driver->heavy_license == '0') {
                throw ValidationException::withMessages([
                    'Este condutor não tem a carta necessária para este veículo. Tente novamente'
                ]);
            }

            Order::create([
                'begin_address' => $incomingFields['begin_address'],
                'end_address' => $incomingFields['end_address'],
                'begin_date' => $incomingFields['begin_date'],
                'end_date' => $incomingFields['end_date'],
                'begin_coordinates' => $beginCoordinates,
                'end_coordinates' => $endCoordinates,
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido criado com sucesso!');

        } catch (ValidationException $e) {
            dd($e);
            return redirect()->route('orders.create')->withErrors($e->validator)->withInput();

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    //TODO: ADD PLACES ATTACHED TO THE KIDS ARRAY
    public function showEditOrderForm(Order $order)
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $places = Place::all();                     //TODO: TO BE CHANGED
        $kids = Kid::with('places')->get();
        //dd($order);

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
            'places' => $places,
            'kids' => $kids,
        ]);
    }

    //TODO: IF EDITED AFTER APPROVED NEEDS REAPPROVAL
    public function editOrder(Order $order, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'begin_address' => ['required', 'string', 'max:255'],
            'begin_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'begin_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],
            'end_address' => ['required', 'string', 'max:255'],
            'end_latitude' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'],
            'end_longitude' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'],   
            'begin_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['required','exists:drivers,user_id'],
            'technician_id' => ['required','exists:users,id'],
        ], $customErrorMessages);

        $incomingFields['begin_address'] = strip_tags($incomingFields['begin_address']);
        $incomingFields['end_address'] = strip_tags($incomingFields['end_address']);

        try {
            $user = User::find($request->input('technician_id'));
            if (!$user || $user->user_type !== 'Técnico') {
                throw ValidationException::withMessages([       //TODO: Check this code
                    'technician_id' => ['O valor do campo selecionado para o técnico é inválido. Tente novamente.']
                ]);
            }

            $vehicle = Vehicle::find($request->input('vehicle_id'));
            $driver = Driver::find($request->input('driver_id'));

            if ($vehicle->heavy_vehicle == '1' && $driver->heavy_license == '0') {
                throw ValidationException::withMessages([
                    'Este condutor não tem a carta necessária para este veículo. Tente novamente.'
                ]);
            }
            
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
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido criado com sucesso!');

        }  catch (ValidationException $e) {
            dd($e);
            return redirect()->route('orders.create')->with('error', 'O valor do campo selecionado para o técnico é inválido. Tente novamente.');
        
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    public function deleteOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return redirect()->route('orders.index')->with('message', 'Pedido apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao apagar o pedido. Tente novamente.');
        }
    }

    public function approveOrder(Order $order, Request $request) {
        //$managerId = Auth::id(); --------> to use on calling this page to get logged in user id

        dd($request);
        $incomingFields = $request->validate([
            'manager_id' => ['required', 'exists:users,id']
        ]);

        try {
            $currentDate = Date::now()->toDateTimeString();
            $order->update([
                'manager_id' => $incomingFields['manager_id'],
                'approved_date' => $currentDate,
            ]);

            return redirect()->route('orders.index')->with('message', 'Dados do pedido atualizados com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao editar os dados do pedido. Tente novamente.');
        }
    }
}
