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
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    // Automatically convert to UTC when saving to the database
    public function setIssueDateAttribute($value)
    {
        $this->attributes['issue_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    public function setExpirationDateAttribute($value)
    {
        $this->attributes['expiration_date'] = Carbon::parse($value)->setTimezone('UTC');
    }

    // Automatically convert back to the app timezone when retrieving
    public function getIssueDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y');
    }

    public function getExpirationDateAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
