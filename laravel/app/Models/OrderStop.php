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
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
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
        return $this->belongsToMany(Kid::class, 'kid_order_stop', 'order_stop_id', 'kid_id')->withPivot('place_id');
    }
}
