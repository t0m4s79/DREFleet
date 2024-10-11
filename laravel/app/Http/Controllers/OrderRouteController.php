<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Driver;
use App\Models\OrderRoute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorMessagesHelper;
use App\Rules\TechnicianUserTypeValidation;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;


class OrderRouteController extends Controller
{
    public function index() //: Response
    {
        $orderRoutes = OrderRoute::all();

        return Inertia::render('OrderRoutes/AllOrderRoutes', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'orderRoutes' => $orderRoutes,
        ]);
    }

    public function showCreateOrderRouteForm()
    {
        $technicians = User::where('user_type', 'Técnico')->get();
        $drivers = Driver::all();

        return Inertia::render('OrderRoutes/NewOrderRoute', [
            'technicians' => $technicians,
            'drivers' => $drivers,
        ]);
    }

    public function createOrderRoute(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        
        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'area_coordinates' => ['required', 'array'],
            'area_coordinates.*.lat' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'], // Latitude validation
            'area_coordinates.*.lng' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'], // Longitude validation
            'area_color' => ['required', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'usual_drivers' => ['array'], // Ensure it's an array first
            'usual_drivers.*' => ['exists:drivers,user_id'], // Check that each item in the array exists in the drivers table
            'usual_technicians' => ['array'],
            'usual_technicians.*' => [
                'exists:users,id',
                new TechnicianUserTypeValidation(),
            ],        
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        $coordinates = $incomingFields['area_coordinates'];

        $usalDrivers = isset($incomingFields['usual_drivers']) ? array_map('strip_tags', $incomingFields['usual_drivers']) : [];
        $usualTechnicians = isset($incomingFields['usual_technicians']) ? array_map('strip_tags', $incomingFields['usual_technicians']) : [];

        DB::beginTransaction();
        try {
            $points = [];

            foreach ($coordinates as $coordinate) {
                $point = new Point($coordinate["lat"], $coordinate["lng"]);
                
                $points[] = $point;
            }

            // Ensure the polygon is closed by adding the first point at the end if necessary
            if ($points[0] !== end($points)) {
                $points[] = $points[0];
            }

            $area = new Polygon([
                new LineString($points),
            ]);

            $orderRoute = OrderRoute::create([
                'name' => $incomingFields['name'],
                'area' => $area,
                'area_color' => $incomingFields['area_color'],
            ]);
            
            $orderRoute->drivers()->attach($usalDrivers);
            $orderRoute->technicians()->attach($usualTechnicians);

            DB::commit();

            return redirect()->route('orderRoutes.index')->with('message', 'Rota com id ' . $orderRoute->id . ' criado com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('orderRoutes.index')->with('error', 'Houve um problema ao tentar criar a rota. Tente novamente.');
        }
    }

    public function showEditOrderRouteForm(OrderRoute $orderRoute): Response
    {
        $orderRoute->load(['drivers', 'technicians']);  // the database stores coordinates as lng lat, so need to reverse it to use as lat lng
        $technicians = User::where('user_type', 'Técnico')->get();
        $drivers = Driver::all();

        return Inertia::render('OrderRoutes/EditOrderRoute', [
            'orderRoute' => $orderRoute,
            'technicians' => $technicians,
            'drivers' => $drivers,
        ]);
    }

    public function editOrderRoute(OrderRoute $orderRoute, Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        $incomingFields = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'area_coordinates' => ['required', 'array'],
            'area_coordinates.*.lat' => ['required', 'numeric', 'between:-90,90', 'regex:/^-?\d{1,2}\.\d{0,15}$/'], // Latitude validation
            'area_coordinates.*.lng' => ['required', 'numeric', 'between:-180,180', 'regex:/^-?\d{1,3}\.\d{0,15}$/'], // Longitude validation
            'area_color' => ['required', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'usual_drivers' => ['array'], // Ensure it's an array first
            'usual_drivers.*' => ['exists:drivers,user_id'], // Check that each item in the array exists in the drivers table
            'usual_technicians' => ['array'],
            'usual_technicians.*' => [
                'exists:users,id',
                new TechnicianUserTypeValidation(),
            ],
        ], $customErrorMessages);

        $incomingFields['name'] = strip_tags($incomingFields['name']);

        $coordinates = $incomingFields['area_coordinates'];

        DB::beginTransaction();
        try {
            $points = [];

            foreach ($coordinates as $coordinate) {
                $point = new Point($coordinate["lat"], $coordinate["lng"]);
                
                $points[] = $point;
            }

            if ($points[0] !== end($points)) {
                $points[] = $points[0];
            }
            
            $area = new Polygon([
                new LineString($points),
            ]);

            $orderRoute->update([
                'name' => $incomingFields['name'],
                'area' => $area,
                'area_color' => $incomingFields['area_color'],
            ]);

            $orderRoute->drivers()->sync($incomingFields['usual_drivers']);
            $orderRoute->technicians()->sync($incomingFields['usual_technicians']);

            DB::commit();

            return redirect()->route('orderRoutes.index')->with('message', 'Dados da rota com id ' . $orderRoute->id . ' atualizados com sucesso!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->route('orderRoutes.index')->with('error', 'Houve um problema ao atualizar os dados da rota com id ' . $orderRoute->user_id . '. Tente novamente.');
        }
    }

    public function deleteOrderRoute($id)
    {
        try {
            $orderRoute = OrderRoute::findOrFail($id);
            $orderRoute->delete();
            
            return redirect()->route('orderRoutes.index')->with('message', 'Rota com id ' . $id . ' eliminada com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('orderRoutes.index')->with('error', 'Houve um problema ao eliminar a rota com id ' . $id . '. Tente novamente.');
        }
    }
}
