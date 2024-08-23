<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model',
        'license_plate',
        'heavy_vehicle',
        'wheelchair_adapted',
        'capacity',
        'fuel_consumption',
        'status',
        'current_month_fuel_requests'
    ];

    public function getHeavyVehicleAttribute($value)
    {
        return $value ? 'Sim' : 'Não';
    }

    public function getWheelchairAdaptedAttribute($value)
    {
        return $value ? 'Sim' : 'Não';
    }
}


