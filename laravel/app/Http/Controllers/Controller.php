<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected $loggedInUserId;

    public function __construct()
    {
        $this->loggedInUserId = Auth::user()->id ?? null;
    }
}
