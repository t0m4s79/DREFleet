<?php

namespace App\Models;

use App\Models\Kid;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//SELECT id, address, known_as, ST_AsText(coordinates) AS coordinates_text FROM places; -> READ BIN FILE OF COORDINATES DIRECTLY IN PHPMYADMIN
//UPDATE places SET coordinates = ST_GeomFromText('POINT(77 21)') WHERE id = 1; -> UPDATE COORDINATES DIRECTLY IN PHPMYADMIN
class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'known_as',
        'place_type',
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => Point::class,
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class, 'kid_place', 'place_id', 'kid_id')->withTimestamps();
    }

    public function orderStops(): HasMany
    {
        return $this->hasMany(OrderStop::class);
    }
}