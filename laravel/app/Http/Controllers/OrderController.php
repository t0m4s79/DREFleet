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
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\KidVehicleValidation;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\ManagerUserTypeValidation;
use App\Rules\OrderDriverLicenseValidation;
use App\Rules\TechnicianUserTypeValidation;
use App\Rules\OrderVehicleCapacityValidation;
use App\Notifications\OrderCreationNotification;
use App\Rules\EntityOrderAvailabilityValidation;
use App\Notifications\OrderRequiresApprovalNotification;
use App\Rules\KidDriverValidation;
use Illuminate\Support\Facades\Gate;

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
        //Gate::authorize('viewAny', Order::class);
        
        Log::channel('user')->info('User accessed orders page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);

        $orders = Order::with(['orderStops', 'occurrences', 'vehicle:id,license_plate', 'driver', 'technician'])->get();

        $orders->each(function ($order) {
            // Format the dates as dd-mm-yyyy
            $order->expected_begin_date = Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i');
            $order->expected_end_date = Carbon::parse($order->expected_end_date)->format('d-m-Y H:i');
            $order->approved_date = $order->approved_date ? Carbon::parse($order->approved_date)->format('d-m-Y H:i') : '-';

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

        if(! Gate::allows('create-order')){
            abort(403);
        };

        Log::channel('user')->info('User accessed order creation page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
        ]);
        
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência')->get();
        $routes = OrderRoute::with(['drivers', 'technicians'])->get();

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

        // if ($request->user()->cannot('create')) {
        //     abort(403);
        // }

        if(! Gate::allows('create-order')){
            abort(403);
        };

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
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date')),
            ],
            'driver_id' => [
                'required',
                'exists:drivers,user_id',
                new OrderDriverLicenseValidation($request->input('vehicle_id')),
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date')),
                new KidDriverValidation($request->input('order_type')),
            ],
            'technician_id' => [
                'required_if:order_type,Transporte de Crianças',
                'exists:users,id',
                'nullable',
                new TechnicianUserTypeValidation(),
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date')),
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

            Log::channel('user')->info('User created an order', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $order->id ?? null,
            ]);

            // Notify managers that order requires approval
            foreach (User::where('user_type', 'Gestor')->get() as $user) {
                $user->notify(new OrderRequiresApprovalNotification($order));
            }

            // Notify users involved of new order
            User::find($incomingFields['driver_id'])->notify(new OrderCreationNotification($order));
            User::find($incomingFields['technician_id'])->notify(new OrderCreationNotification($order));

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel('usererror')->error('Error creating order', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.index')->with('error', 'Houve um problema ao criar o pedido. Tente novamente.');
        }
    }

    public function showEditOrderForm(Order $order)
    {

        if(! Gate::allows('edit-order')){
            abort(403);
            //return redirect()->route('orders.index')->with('error', 'Não tem permissões para editar o pedido.');
        };
        
        Log::channel('user')->info('User accessed order edit page', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $order->id ?? null,
            ]);

        $order->load(['orderStops.place', 'orderStops.kids'])->get();

        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('user_type', 'Técnico')->get();
        $managers = User::where('user_type', 'Gestor')->get();
        $places = Place::all();
        $kids = Kid::with('places')->get();
        $otherPlaces = Place::whereNot('place_type', 'Residência')->get();
        $routes = OrderRoute::with(['drivers', 'technicians'])->get();

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

        if(! Gate::allows('edit-order')){
            abort(403);
        };

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
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date'), $order->id),
            ],            
            'driver_id' => [
                'required',
                'exists:drivers,user_id',
                new OrderDriverLicenseValidation($request->input('vehicle_id')),
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date'), $order->id),
                new KidDriverValidation($request->input('order_type')),
            ],
            'technician_id' => [
                'required_if:order_type,Transporte de Crianças',
                'nullable',
                'exists:users,id',
                new TechnicianUserTypeValidation(),                
                new EntityOrderAvailabilityValidation($request->input('expected_begin_date'),$request->input('expected_end_date'), $order->id),
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
                //TODO: IF AN ORDER IS EDITED, SHOULD IT NEED REAPPROVAL
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

            Log::channel('user')->info('User edited an order', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $order->id ?? null,
            ]);

            return redirect()->route('orders.index')->with('message', 'Dados do pedido com ' . $order->id . ' atualizados com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('usererror')->error('Error editing order', [
                'order_id' => $order->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.index')->with('error', 'Houve um problema ao editar os dados do pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    public function deleteOrder($id)
    {
        if(! Gate::allows('delete-order')){
            abort(403);
        };

        try {
            $order = Order::findOrFail($id);
            $order->delete();

            Log::channel('user')->info('User deleted an order', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $id ?? null,
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' apagado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error deleting order', [
                'order_id' => $id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.index')->with('error', 'Houve um problema ao apagar o pedido com id ' . $id . '. Tente novamente.');
        }
    }

    //TODO: Add Administrators Role
    public function approveOrder(Order $order, Request $request) 
    {

        if(! Gate::allows('approve-order')){
            abort(403);
        };

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

            Log::channel('user')->info('User approved an order', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $order->id ?? null,
            ]);

            return redirect()->route('orders.index')->with('message', 'Pedido com id ' . $order->id . ' aprovado com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error approving order', [
                'order_id' => $order->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.index')->with('error', 'Houve um problema ao aprovar o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    public function removeOrderApproval(Order $order, Request $request) 
    {
        if(! Gate::allows('approve-order')){
            abort(403);
        };
        
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

            Log::channel('user')->info('User unapproved an order', [
                'auth_user_id' => $this->loggedInUserId ?? null,
                'order_id' => $order->id ?? null,
            ]);

            return redirect()->route('orders.index')->with('message', 'Aprovação removida do pedido com id ' . $order->id . ' com sucesso!');

        } catch (\Exception $e) {
            Log::channel('usererror')->error('Error removing order approval', [
                'order_id' => $order->id ?? null,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.index')->with('error', 'Houve um problema ao remover a aprovação o pedido com id ' . $order->id . '. Tente novamente.');
        }
    }

    //TODO: MOVE TO SEPARATE CONTROLLER (ORDER REPORTS)
    // public function orderStarted(Order $order) 
    // {
    //     try {
    //         $order->update([
    //             'actual_begin_date' => now(),
    //         ]);

    //         Log::channel('user')->info('Order marked as started', [
    //             'auth_user_id' => $this->loggedInUserId ?? null,
    //             'order_id' => $order->id ?? null,
    //         ]);

    //         //TODO: REDIRECT

    //     } catch (\Exception $e) {
    //         Log::channel('usererror')->error('Error marking order as started', [
    //             'order_id' => $order->id ?? null,
    //             'exception' => $e->getMessage(),
    //             'stack_trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->route('orders.index')->with('error', 'Houve um problema ao começar o pedido com id ' . $order->id . '. Tente novamente.');
    //     }
    // }

    // public function orderEnded(Order $order) 
    // {
    //     try {
    //         $order->update([
    //             'actual_end_date' => now(),
    //         ]);

    //         Log::channel('user')->info('Order marked as ended', [
    //             'auth_user_id' => $this->loggedInUserId ?? null,
    //             'order_id' => $order->id ?? null,
    //         ]);

    //         //TODO: REDIRECT

    //     } catch (\Exception $e) {
    //         Log::channel('usererror')->error('Error marking order as ended', [
    //             'order_id' => $order->id ?? null,
    //             'exception' => $e->getMessage(),
    //             'stack_trace' => $e->getTraceAsString(),
    //         ]);

    //         return redirect()->route('orders.index')->with('error', 'Houve um problema ao acabar o pedido com id ' . $order->id . '. Tente novamente.');
    //     }
    // }

    public function showOrderOccurrences(Order $order)
    {
        Log::channel('user')->info('User accessed order occurrences page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'order_id' => $order->id ?? null,
        ]);

        $order->load(['occurrences', 'vehicle', 'driver']);

        $order->expected_begin_date = Carbon::parse($order->expected_begin_date)->format('d-m-Y');

        $order->occurrences->each(function ($occurrence) {
            $occurrence->vehicle_towed = $occurrence->vehicle_towed == 1 ? 'Sim' : 'Não';
        });


        return Inertia::render('Orders/OrderOccurrences', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'order' => $order
        ]);
    }

    public function showOrderStops(Order $order) 
    {
        Log::channel('user')->info('User accessed order stops page', [
            'auth_user_id' => $this->loggedInUserId ?? null,
            'order_id' => $order->id ?? null,
        ]);

        $order->load(['orderStops', 'orderStops.kids']);
        $order->orderStops->each(function ($stop) {
            $stop->expected_arrival_date = $stop->expected_arrival_date ? Carbon::parse($stop->expected_arrival_date)->format('d-m-Y H:i') : null;
            $stop->actual_arrival_date = $stop->actual_arrival_date ? Carbon::parse($stop->actual_arrival_date)->format('d-m-Y H:i') : null;
        });

        return Inertia::render('Orders/OrderStops', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'order' => $order,
        ]);
    }
}
