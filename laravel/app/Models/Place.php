<?php

namespace App\Models;

use App\Models\Kid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//TODO: check if a place should always belong to a kid
class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'address',
        'known_as',
        'latitude',
        'longitude'
    ];

    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class, 'kid_place', 'place_id', 'kid_id')->withTimestamps();
    }
}
