<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Driver extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'heavy_license'
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

    public function getStatusAttribute(): int
    {
        return $this->user->status_code;
    }
}
