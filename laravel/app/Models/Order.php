<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'expected_begin_date',
        'expected_end_date',
        'trajectory',
        'order_type',

        'vehicle_id',
        'driver_id',
        'technician_id',
        'order_route_id',

        'manager_id',
        'approved_date',
    ];

    protected $casts = [
        'end_coordinates' => Point::class,
        'begin_coordinates' => Point::class,
    ];
    
    public function orderStops(): HasMany
    {
        return $this->hasMany(OrderStop::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'user_id');
    }
}
