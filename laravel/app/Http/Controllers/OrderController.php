<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Kid;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\OrderRoute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\KidVehicleValidation;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\ManagerUserTypeValidation;
use App\Rules\OrderDriverLicenseValidation;
use App\Rules\TechnicianUserTypeValidation;
use App\Rules\OrderVehicleCapacityValidation;

class OrderController extends Controller
{
    //TODO: IF SOMEONE EDITS A PLACE OR KID OR ROUTE HOW DOES ORDER BEHAVE?
    protected $orderStopController;

    public function __construct(OrderStopController $orderStopController)
    {
        $this->orderStopController = $orderStopController;
    }

    public function index()
    {
        $orders = Order::with(['orderStops'])->get();

        return Inertia::render('Orders/AllOrders',[
           'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'orders' => $orders,
        ]);
    }

    public function showCreateOrderForm()
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência')->get();
        $routes = OrderRoute::all();

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
            'otherPlaces' => $otherPlaces,
            'orderRoutes' => $routes,
        ]);
    }

    //TODO: CAN DRIVER/VEHICLE BE NULL??
    //TODO: SNACKBAR
    public function createOrder(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'expected_begin_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => [
                'required',
                'exists:drivers,user_id',
                new OrderDriverLicenseValidation($request->input('vehicle_id')),
            ],
            'technician_id' => [
                'required_if:order_type,Transporte de Crianças',
                'exists:users,id',
                new TechnicianUserTypeValidation(),
            ],
            'order_route_id' => ['nullable', 'exists:order_routes,id'],
            'places' => ['required', 'array'], // Ensure 'places' is an array
            'places.*' => ['array'],           // Ensure each item in 'places' is an array
            'places.*.place_id' => ['required', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'places.*.kid_id' => [
                'nullable',           // Validate that 'kid_id' is optional but must exist if provided
                'exists:kids,id',
                new KidVehicleValidation($request->input('order_type'), $request->input('vehicle_id')),
            ],

            new OrderVehicleCapacityValidation(
                $request->input('order_type'),
                $request->input('vehicle_id'),
                $request->input('places'),
                $request->input('technician_id')
            ),
            
        ] ,$customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'expected_begin_date' => $incomingFields['expected_begin_date'],
                'expected_end_date' => $incomingFields['expected_end_date'],
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
                'status' => 'Por aprovar',
            ]);

            //TODO: PLANNED ARRIVAL DATE??
            // Create the order stops
            foreach ($incomingFields['places'] as $place) {
                $orderStopRequest = new Request([
                    'order_id' => $order->id,
                    'place_id' => $place['place_id'],
                    'kid_id' => $place['kid_id'] ?? null, // Use null if kid_id is not set
                ]);

                $this->orderStopController->createOrderStop($orderStopRequest);
            }

            DB::commit();

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    public function showEditOrderForm(Order $order)
    {
        $order->load('orderStops.place')->get();
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $places = Place::all();
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência')->get();
        $routes = OrderRoute::all();

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
            'otherPlaces' => $otherPlaces,
            'orderRoutes' => $routes,
        ]);
    }

    public function editOrder(Order $order, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'expected_begin_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date'],
            'trajectory' => ['required', 'json'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => [
                'required',
                'exists:drivers,user_id',
                new OrderDriverLicenseValidation($request->input('vehicle_id')),
            ],
            'technician_id' => [
                'required_if:order_type,Transporte de Crianças',
                'exists:users,id',
                new TechnicianUserTypeValidation(),                
            ],            
            'order_route_id' => ['nullable', 'exists:order_routes,id'],
            'addPlaces' => ['nullable', 'array'], // Ensure 'places' is an array
            'addPlaces.*' => ['array'],           // Ensure each item in 'places' is an array
            'addPlaces.*.place_id' => ['required', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'places.*.kid_id' => [
                'nullable',           // Validate that 'kid_id' is optional but must exist if provided
                'exists:kids,id',
                new KidVehicleValidation($request->input('order_type'), $request->input('vehicle_id')),
            ],
            'removePlaces' => ['nullable', 'array'], // Ensure 'places' is an array

            new OrderVehicleCapacityValidation(
                $request->input('order_type'),
                $request->input('vehicle_id'),
                $request->input('places'),
                $request->input('technician_id')
            ),

        ], $customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;
        $incomingFields['addPlaces'] = $incomingFields['addPlaces'] ?? null;
        $incomingFields['removePlaces'] = $incomingFields['removePlaces'] ?? null;

        DB::beginTransaction();
        try {
            $order->update([
                'expected_begin_date' => $incomingFields['expected_begin_date'],
                'expected_end_date' => $incomingFields['expected_end_date'],
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
                'status' => 'Por aprovar'
            ]);

            //TODO: PLANNED ARRIVAL DATE
            // Create the new order stops
            if($incomingFields['addPlaces'] != null) {
                foreach ($incomingFields['addPlaces'] as $place) {
                    $orderStopRequest = new Request([
                        'order_id' => $order->id,
                        'place_id' => $place['place_id'],
                        'kid_id' => $place['kid_id'] ?? null, // Use null if kid_id is not set
                    ]);

                    $this->orderStopController->createOrderStop($orderStopRequest);
                }
            }

            // Delete the removed order stops
            if($incomingFields['removePlaces'] != null){
                foreach ($incomingFields['removePlaces'] as $placeId) {
                    $this->orderStopController->deleteOrderStop($placeId);
                }
            }

            DB::commit();

            return redirect()->route('orders.index')->with('message', 'Dados do pedido com ' . $order->id . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao editar os dados do pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    public function deleteOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . 'apagado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao apagar o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    //TODO: Add Administrators Role
    public function approveOrder(Order $order, Request $request) 
    {
        //$managerId = Auth::id(); --------> to use on calling this page to get logged in user id

        $incomingFields = $request->validate([
            'manager_id' => [
                'required', 
                'exists:users,id', 
                new ManagerUserTypeValidation(),
            ]
        ]);

        try {
            $order->update([
                'manager_id' => $incomingFields['manager_id'],
                'approved_date' => now(),
                'status' => 'Aprovado'
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' aprovado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao aprovar o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    public function removeOrderApproval(Order $order, Request $request) 
    {
        //$managerId = Auth::id(); --------> to use on calling this page to get logged in user id

        $request->validate([
            'manager_id' => [
                'required', 
                'exists:users,id', 
                new ManagerUserTypeValidation(),
            ]
        ]);

        try {
            $order->update([
                'manager_id' => null,
                'approved_date' => null,
                'status' => 'Por aprovar'
            ]);

            return redirect()->route('orders.index')->with('message', 'Aprovação removida do pedido com id ' . $order->id . ' com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao remover a aprovação o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }
}
