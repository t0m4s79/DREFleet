<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function example()
    {
        return Inertia::render('Routes/Example');
    }
}
