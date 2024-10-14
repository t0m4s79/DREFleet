<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Driver extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false; // Disable auto-incrementing as 'user_id' is not an auto-increment column (user_id = id in users table)
    protected $keyType = 'int'; // Set the key type to integer

    protected $fillable = [
        'user_id',
        'license_number',
        'heavy_license',
        'heavy_license_type',
        'license_expiration_date',
    ];

    protected $appends = [
        'name',        // User's name
        'email',       // User's email
        'phone',       // User's phone number
        'status',
    ];

    protected $hidden = [
        'user',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id'); // Specify the foreign key explicitly
    }

    public function orderRoutes(): BelongsToMany
    {
        return $this->belongsToMany(OrderRoute::class)->withTimestamps();
    }

    // Define inverse polymorphic relationship
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'related_entity');
    }

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->user->phone ?? null;
    }

    public function getStatusAttribute(): string
    {
        return $this->user->status;
    }
}
