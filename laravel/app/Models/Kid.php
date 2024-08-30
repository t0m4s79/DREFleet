<?php

namespace App\Models;

use App\Models\User;
use App\Models\Place;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kid extends Model                 //TODO: relations with places
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

    //TECHNICIAN-KID RELATION
    //TECHNICIAN DOESN'T NEED A SEPARATE TABLE FROM USERS BECAUSE, UNLIKE THE DRIVERS, IT DOESN'T HAVE ANY SPECIFIC ATTRIBUTES
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('priority')->withTimestamps();
    }
}
