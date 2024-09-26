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
    public $incrementing = false; // Disable auto-incrementing as 'user_id' is not an auto-increment column
    protected $keyType = 'int'; // Set the key type to integer

    protected $fillable = [
        'user_id',
        'heavy_license',
        'heavy_license_type',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    public function getPhoneAttribute(): string
    {
        return $this->user->phone;
    }

    public function getStatusAttribute(): string
    {
        return $this->user->status;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderRoutes(): BelongsToMany
    {
        //return $this->belongsToMany(User::class)->withPivot('priority')->withTimestamps();
        return $this->belongsToMany(OrderRoute::class)->withTimestamps();
    }
}
