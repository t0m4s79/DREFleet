<?php

namespace App\Models;

use Illuminate\Support\Carbon;
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
        'expected_time',
        'distance',
        'trajectory',
        'order_type',
        'status',

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

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    // Automatically convert to UTC when saving to the database
    public function setExpectedBeginDateAttribute($value)
    {
        $this->attributes['expected_begin_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setExpectedEndDateAttribute($value)
    {
        $this->attributes['expected_end_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setApprovedDateAttribute($value)
    {
        $this->attributes['approved_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    // Automatically convert back to the app timezone when retrieving
    public function getExpectedBeginDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }

    public function getExpectedEndDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }

    public function getApprovedDateAttribute($value)
    {
        // Check if the value is null or empty
        if (is_null($value) || $value === '') {
            return '-'; // Return '-' if the date is null or empty
        }

        // Return the date in the application's timezone
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i');
    }
    
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

    public function occurrences(): HasMany
    {
        return $this->hasMany(OrderOccurrence::class); // Specify the foreign key explicitly
    }

    // Define inverse polymorphic relationship
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'related_entity');
    }
}
