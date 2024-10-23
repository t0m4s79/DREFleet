<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area',
        'area_color',
    ];

    protected $casts = [
        'area' => Polygon::class,
    ];

    
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class)->withTimestamps();
    }

    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
