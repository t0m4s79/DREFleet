<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Kid;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function ordersTechnician(): HasMany
    {
        return $this->hasMany(Order::class, 'technician_id');
    }

    public function ordersManager(): HasMany
    {
        return $this->hasMany(Order::class, 'manager_id');
    }

    // Order routes usual technician table (order_route_user)
    public function orderRoutes(): BelongsToMany
    {
        return $this->belongsToMany(OrderRoute::class)->withTimestamps();
    }

    // User's own notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    
    // Notifications about the user as a related entity
    public function relatedNotifications()
    {
        return $this->morphMany(Notification::class, 'related_entity');
    }
}
