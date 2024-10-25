<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleRefuelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'quantity',
        'cost_per_unit',
        'total_cost',
        'kilometrage',
        'fuel_type',
        'request_type',
        'monthly_request_number',
        'vehicle_id',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
