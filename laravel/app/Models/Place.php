<?php

namespace App\Models;

use App\Models\Kid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//TODO: check if a place should always belong to a kid
class Place extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'address',
        'known_as',
        'latitude',
        'longitude',
        'kid_id'
    ];

    protected $appends = [
        'name',
    ];

    protected $hidden = [
        'kid',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class, 'kid_id');
    }

    public function getNameAttribute(): string
    {
        return $this->kid->name;
    }
}
