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
use App\Models\OrderStop;
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
use Carbon\Carbon;

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
        $orders = Order::with(['orderStops', 'occurrences'])->get();

        $orders->each(function ($order) {
            // Format the dates as dd-mm-yyyy
            $order->expected_begin_date = \Carbon\Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i');
            $order->expected_end_date = \Carbon\Carbon::parse($order->expected_end_date)->format('d-m-Y H:i');
            $order->approved_date = $order->approved_date ? \Carbon\Carbon::parse($order->approved_date)->format('d-m-Y H:i') : '-';
            $order->created_at = \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i');
            $order->updated_at = \Carbon\Carbon::parse($order->updated_at)->format('d-m-Y H:i');

            $order->order_route_id = $order->order_route_id ?? '-';
        });

        // Render the view with the formatted orders and flash messages
        return Inertia::render('Orders/AllOrders', [
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

    public function createOrder(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $kidsCount = count($request->input('places.*.kid_id', []));
        $hasTechnician = $request->input('technician_id') ? 1 : 0;
        $totalPassengers = $kidsCount + $hasTechnician;

        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'expected_begin_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date', 'after:expected_begin_date'],
            'expected_time' => ['required', 'min:0'], //in seconds
            'distance' => ['required', 'min:0'],      //in meters
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => [
                'required',
                'exists:vehicles,id',
                new OrderVehicleCapacityValidation($totalPassengers, $request->input('order_type')),
            ],
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
            'places.*.stop_number' => ['required', 'integer', 'min:0'],
            'places.*.time' => ['required', 'min:0'],                 //time from previous stop
            'places.*.distance' => ['required', 'min:0'],             //distance from previous stop
            'places.*.place_id' => ['required', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'places.*.kid_id' => [
                'nullable',           // Validate that 'kid_id' is optional but must exist if provided
                'exists:kids,id',
                new KidVehicleValidation($request->input('order_type'), $request->input('vehicle_id')),
            ],
            
        ] ,$customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'expected_begin_date' => $incomingFields['expected_begin_date'],
                'expected_end_date' => $incomingFields['expected_end_date'],
                'expected_time' => $incomingFields['expected_time'],
                'distance' => $incomingFields['distance'],
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
                'status' => 'Por aprovar',
            ]);

            // Calculate the expected arrival of each stop
            $expectedArrivalDate = Carbon::parse($incomingFields['expected_begin_date']);

            // Create the order stops
            foreach ($incomingFields['places'] as $place) {
                $expectedArrivalDate = $expectedArrivalDate->addSeconds($place['time']);
                $orderStopRequest = new Request([
                    'order_id' => $order->id,
                    'stop_number' => $place['stop_number'],
                    'place_id' => $place['place_id'],
                    'time_from_previous_stop' => $place['time'],
                    'distance_from_previous_stop' => $place['distance'],
                    'expected_arrival_date' => $expectedArrivalDate,
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

        $kidsCount = count($request->input('places.*.kid_id', []));
        $hasTechnician = $request->input('technician_id') ? 1 : 0;
        $totalPassengers = $kidsCount + $hasTechnician;

        $incomingFields = $request->validate([
            'expected_begin_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date', 'after:expected_begin_date'],
            'expected_time' => ['required', 'min:0'],
            'distance' => ['required', 'min:0'],
            'trajectory' => ['required', 'json'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => [
                'required',
                'exists:vehicles,id',
                new OrderVehicleCapacityValidation($totalPassengers, $request->input('order_type')),
            ],            
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
            'places_changed' => ['required', 'boolean'],
            'places' => ['required_if:places_changed,true', 'array'], // Ensure 'places' is an array
            'places.*' => ['array'],           // Ensure each item in 'places' is an array
            'places.*.stop_number' => ['required_if:places_changed,true', 'integer', 'min:0'],
            'places.*.time' => ['required_if:places_changed,true', 'min:0'],                 //time from previous stop
            'places.*.distance' => ['required_if:places_changed,true', 'min:0'],             //distance from previous stop
            'places.*.place_id' => ['required_if:places_changed,true', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'places.*.kid_id' => [
                'nullable',           // Validate that 'kid_id' is optional but must exist if provided
                'exists:kids,id',
                new KidVehicleValidation($request->input('order_type'), $request->input('vehicle_id')),
            ],

        ], $customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;

        DB::beginTransaction();
        try {
            $order->update([
                'expected_begin_date' => $incomingFields['expected_begin_date'],
                'expected_end_date' => $incomingFields['expected_end_date'],
                'expected_time' => $incomingFields['expected_time'],
                'distance' => $incomingFields['distance'],
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
                'status' => 'Por aprovar'
            ]);

            //TODO: OPTIMIZE THIS -> SOME WAY OF DELETING ONLY THE NEEDED WHILE UPDATING THE EXISTING AND CREATING NEW ONES
            //TODO:               -> RIGHT NOW IT DELETES EVERY STOP IN THE ORDER AND THEN CREATES EVERY STOP AGAIN(INCLUDING ONES THAT ALREADY EXISTED)
            if($incomingFields['places_changed']) {
                OrderStop::where('order_id', $order->id)->delete();

                // Calculate the expected arrival of each stop
                $expectedArrivalDate = Carbon::parse($incomingFields['expected_begin_date']);
                
                // Create the order stops
                foreach ($incomingFields['places'] as $place) {
                    $expectedArrivalDate = $expectedArrivalDate->addSeconds((float) $place['time']);
                    $orderStopRequest = new Request([
                        'order_id' => $order->id,
                        'stop_number' => $place['place_id'],
                        'place_id' => $place['place_id'],
                        'time_from_previous_stop' => $place['time'],
                        'distance_from_previous_stop' => $place['distance'],
                        'expected_arrival_date' => $expectedArrivalDate,
                        'kid_id' => $place['kid_id'] ?? null, // Use null if kid_id is not set
                    ]);

                    $this->orderStopController->createOrderStop($orderStopRequest);
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

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' apagado com sucesso!');

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

    public function showOrderOccurrences(Order $order)
    {
        $order->load(['occurrences', 'vehicle', 'driver']);

        // Format the fields for each entry
        $order->occurrences->each(function ($occurrence) {
            $occurrence->created_at = \Carbon\Carbon::parse($occurrence->created_at)->format('d-m-Y');
            $occurrence->updated_at = \Carbon\Carbon::parse($occurrence->updated_at)->format('d-m-Y');
        });

        $order->expected_begin_date = \Carbon\Carbon::parse($order->expected_begin_date)->format('d-m-Y');

        return Inertia::render('Orders/OrderOccurrences', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'order' => $order
        ]);
    }
}
