<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area_coordinates'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
