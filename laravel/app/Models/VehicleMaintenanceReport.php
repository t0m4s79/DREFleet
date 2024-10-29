<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleMaintenanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'begin_date',
        'end_date',
        'type',
        'description',
        'kilometrage',
        'total_cost',
        'items_cost',
        'service_provider',
        'status',
        'vehicle_id',
    ];

    protected $casts = [
        'items_cost' => 'array',
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
