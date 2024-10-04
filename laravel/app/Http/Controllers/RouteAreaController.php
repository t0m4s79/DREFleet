<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorMessagesHelper;
use App\Models\RouteArea;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class RouteAreaController extends Controller
{
    public function index(){
        $routes = RouteArea::all();

        return Inertia::render('',[             //TODO: Add all routes page
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'routes' => $routes
        ]);
    }

    public function showCreateRouteAreaForm(){
        return Inertia::render('RouteAreas/NewRouteArea') ;
    }

    public function createRouteArea(Request $request)
    {
        $customErrorMessages = ErrorMessagesHelper::getErrorMessages();
        //dd($request);
        $incomingFields = $request->validate([
            'name' => 'required|string',
            'area_coordinates' => 'required|array',
        ]);

        $areaCoordinates = $incomingFields['area_coordinates'];
        $points = array_map(function($coord) {
            return new Point($coord['lat'], $coord['lng']);
        }, $areaCoordinates);

        $polygon = new Polygon([new LineString($points)]);

        try{
            $routeArea = RouteArea::create([
                'name' => $request->name,
                'area_coordinates' => $polygon,
            ]);
            
            return redirect()->route('/dashboard')->with('success', 'Route created successfully.');

        } catch(\Exception $e) {
            Log::error($e);
        }
    }
}
