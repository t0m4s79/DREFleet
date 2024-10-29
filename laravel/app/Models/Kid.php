<?php

namespace App\Models;

use App\Models\User;
use App\Models\Place;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kid extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'wheelchair',
        'name',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function places(): BelongsToMany 
    {
        return $this->belongSToMany(Place::class, 'kid_place', 'kid_id', 'place_id')->withTimestamps();
    }

    public function orderStops(): BelongsToMany
    {
        return $this->belongsToMany(OrderStop::class)->withPivot('place_id');
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(KidPhoneNumber::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(KidEmail::class);
    }

    // Define inverse polymorphic relationship
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'related_entity');
    }

}