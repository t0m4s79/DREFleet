<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleKilometrageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'begin_kilometrage',
        'end_kilometrage',
        'vehicle_id',
        'driver_id',
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
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    // Automatically convert back to the app timezone when retrieving
    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'user_id');
    }
}
