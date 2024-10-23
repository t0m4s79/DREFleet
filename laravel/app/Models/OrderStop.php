<?php

namespace App\Models;

use App\Models\Place;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'expected_arrival_date',
        'actual_arrival_date',
        'time_from_previous_stop',
        'distance_from_previous_stop',
        'stop_number',
        'order_id',
        'place_id',
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
    public function setExpectedArrivalDateAttribute($value)
    {
        $this->attributes['expected_arrival_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setActualArrivalDateAttribute($value)
    {
        $this->attributes['actual_arrival_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    // Automatically convert back to the app timezone when retrieving
    public function getExpectedArrivalDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }

    public function getActualArrivalDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class)->withPivot('place_id');
    }
}
