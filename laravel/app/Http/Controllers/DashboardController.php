<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        return Inertia::render('Dashboard', [
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
        ]);
    }
}
