<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KidPhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'phone',
        'owner_name',
        'relationship_to_kid',
        'preference',
        'kid_id',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }
}
