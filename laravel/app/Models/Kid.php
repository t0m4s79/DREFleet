<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function places(): HasMany 
    {
        return $this->hasMany(Place::class);
    }
}
