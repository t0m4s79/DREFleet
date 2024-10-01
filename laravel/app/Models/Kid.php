<?php

namespace App\Models;

use App\Models\User;
use App\Models\Place;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//TODO: SHOULD KID HAVE MULTIPLE PHONE NUMBERS AND EMAILS (NEW TABLES) -> YES!!!
class Kid extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'wheelchair',
        'name',
        'phone',
        'email'
    ];

    public function places(): BelongsToMany 
    {
        return $this->belongSToMany(Place::class, 'kid_place', 'kid_id', 'place_id')->withTimestamps();
    }

    public function orderStops(): BelongsToMany
    {
        return $this->belongsToMany(OrderStop::class)->withPivot('place_id');
    }
}
