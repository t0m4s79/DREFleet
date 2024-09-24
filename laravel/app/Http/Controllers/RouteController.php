<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouteController extends Controller
{
    public function index(){
        $routes = Route::all();

        return Inertia::render('',[             //TODO: Add all routes page
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'routes' => $routes
        ]);
    }


}
