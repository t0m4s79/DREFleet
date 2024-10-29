<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'issue_date',
        'expiration_date',
        'expired',
        'vehicle_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',  // This will allow Laravel to cast the JSON to an array
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
