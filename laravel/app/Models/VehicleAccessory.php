<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAccessory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'condition',
        'expiration_date',
        'vehicle_id',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    // Automatically convert to UTC when saving to the database
    public function setExpirationDateAttribute($value)
    {
        $this->attributes['expiration_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    // Automatically convert back to the app timezone when retrieving
    public function getExpirationDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }
    
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
