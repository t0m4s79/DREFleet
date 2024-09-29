<?php

namespace App\Models;

use App\Models\Place;
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
        'address',
        'coordinates',
        'order_id',
        'place_id'
    ];

    protected $casts = [
        'coordinates' => Point::class,
    ];

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
