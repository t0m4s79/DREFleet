<?php

namespace App\Models;

use App\Models\Kid;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//SELECT id, address, known_as, ST_AsText(coordinates) AS coordinates_text FROM places; -> READ BIN FILE OF COORDINATES DIRECTLY IN PHPMYADMIN
//UPDATE places SET coordinates = ST_GeomFromText('POINT(77 21)') WHERE id = 1; -> UPDATE COORDINATES DIRECTLY IN PHPMYADMIN
//TODO: check if a place should always belong to a kid
class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        //'id',                 //TODO:NEEDED?
        'address',
        'known_as',
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => Point::class,
    ];

    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class, 'kid_place', 'place_id', 'kid_id')->withTimestamps();
    }
}
