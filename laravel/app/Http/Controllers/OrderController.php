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
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use Illuminate\Support\Facades\Auth;

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
        $orders = Order::all();

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
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência');
        $routes = OrderRoute::all();

        return Inertia::render('Orders/NewOrder', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'technicians' => $technicians,
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
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => [
                'required',
                'exists:drivers,user_id',

                function ($attribute, $value, $fail) use ($request) {
                    $vehicle = Vehicle::find($request->input('vehicle_id'));
                    $driver = Driver::find($value);
        
                    if ($vehicle && $vehicle->heavy_vehicle == '1') {
                        if ($driver && $driver->heavy_license == '0') {
                            $fail('Este condutor não tem a carta necessária para este veículo.');
                        } elseif ($vehicle->heavy_type == 'Passageiros' && $driver->heavy_license_type == 'Mercadorias') {
                            $fail('Este condutor não tem a carta necessária para este veículo.');
                        }
                    }
                },
            ],
            'technician_id' => [
                'required',
                'exists:users,id',

                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || $user->user_type !== 'Técnico') {
                        $fail('O valor selecionado para o campo do técnico é inválido');
                    }
                },
            ],
            'order_route_id' => ['nullable', 'exists:order_routes,id'],
            'places' => ['required', 'array'], // Ensure 'places' is an array
            'places.*' => ['array'],           // Ensure each item in 'places' is an array
            'places.*.place_id' => ['required', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'places.*.kid_id' => ['nullable', 'exists:kids,id'], // Validate that 'kid_id' is optional but must exist if provided
        ], $customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;

        try {
            $order = Order::create([
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
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

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    public function showEditOrderForm(Order $order)
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência');
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
            'kids' => $kids,
            'otherPlaces' => $otherPlaces,
            'orderRoutes' => $routes,
        ]);
    }

    //TODO: IF EDITED AFTER APPROVED NEEDS REAPPROVAL
    public function editOrder(Order $order, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();

        $incomingFields = $request->validate([
            'trajectory' => ['required', 'json'],
            'order_type' => ['required', Rule::in(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros'])],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => [
                'required',
                'exists:drivers,user_id',

                function ($attribute, $value, $fail) use ($request) {
                    $vehicle = Vehicle::find($request->input('vehicle_id'));
                    $driver = Driver::find($value);
        
                    if ($vehicle && $vehicle->heavy_vehicle == '1') {
                        if ($driver && $driver->heavy_license == '0') {
                            $fail('Este condutor não tem a carta necessária para este veículo.');
                        } elseif ($vehicle->heavy_type == 'Passageiros' && $driver->heavy_license_type == 'Mercadorias') {
                            $fail('Este condutor não tem a carta necessária para este veículo.');
                        }
                    }
                },
            ],
            'technician_id' => [
                'required',
                'exists:users,id',

                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || $user->user_type !== 'Técnico') {
                        $fail('O valor selecionado para o campo do técnico é inválido');
                    }
                },
            ],            
            'order_route_id' => ['nullable', 'exists:order_routes,id'],
            'addPlaces' => ['nullable', 'array'], // Ensure 'places' is an array
            'addPlaces.*' => ['array'],           // Ensure each item in 'places' is an array
            'addPlaces.*.place_id' => ['required', 'exists:places,id'], // Validate that 'place_id' exists in the places table
            'addPlaces.*.kid_id' => ['nullable', 'exists:kids,id'], // Validate that 'kid_id' is optional but must exist if provided
            'removePlaces' => ['nullable', 'array'], // Ensure 'places' is an array
        ], $customErrorMessages);

        $incomingFields['order_route_id'] = $incomingFields['order_route_id'] ?? null;
        $incomingFields['addPlaces'] = $incomingFields['addPlaces'] ?? null;
        $incomingFields['removePlaces'] = $incomingFields['removePlaces'] ?? null;

        try {
            $order->update([
                'trajectory' => $incomingFields['trajectory'],
                'order_type' => $incomingFields['order_type'],
                'vehicle_id' => $incomingFields['vehicle_id'],
                'driver_id' => $incomingFields['driver_id'],
                'technician_id' => $incomingFields['technician_id'],
                'order_route_id' => $incomingFields['order_route_id'],
            ]);

            //TODO: PLANNED ARRIVAL DATE??
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

            return redirect()->route('orders.index')->with('message', 'Dados do pedido com ' . $order->id . ' atualizados com sucesso!');

        } catch (\Exception $e) {
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
                function ($attribute, $value, $fail) {
                    // Check if the manager_id belongs to a user with 'Gestor' type
                    $user = User::find($value);
                    if (!$user || $user->user_type !== 'Gestor') {
                        $fail('O utilizador com id ' . $value . ' selecionado não está autorizado a aprovar pedidos.');
                    }
                }
            ]
        ]);

        try {
            $order->update([
                'manager_id' => $incomingFields['manager_id'],
                'approved_date' => now(),
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' aprovado com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orders.index')->with('error', 'Houve um problema ao aprovar o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }
}
