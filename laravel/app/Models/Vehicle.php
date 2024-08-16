<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'make',
        'model',
        'license_plate',
        'heavy_vehicle',
        'wheelchair_adapted',
        'capacity',
        'fuel_consumption',
        'status_code',
        'current_month_fuel_requests'
    ];
}


