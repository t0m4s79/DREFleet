<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'make',
        'model',
        'license_plate',
        'year',
        'heavy_vehicle',
        'heavy_type',
        'wheelchair_adapted',
        'wheelchair_certified',
        'capacity',
        'fuel_consumption',
        'status',
        'current_month_fuel_requests',
        'fuel_type',
        'current_kilometrage',
        'image_path',
    ];
    
    // This array tells what attributes shouldn't be showed when calling the model instance
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // public function getHeavyVehicleAttribute($value)
    // {
    //     return $value ? 'Sim' : 'Não';
    // }

    // public function getWheelchairAdaptedAttribute($value)
    // {
    //     return $value ? 'Sim' : 'Não';
    // }

    // public function getWheelchairCertifiedAttribute($value)
    // {
    //     return $value ? 'Sim' : 'Não';
    // }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function vehicleDocuments(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function vehicleAccessories(): HasMany
    {
        return $this->hasMany(VehicleAccessory::class);
    }
}


