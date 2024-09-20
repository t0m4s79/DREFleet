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

//TODO: SHOULD USER BE ALLOWED TO HAVE MULTIPLE PHONE NUMBERS AND EMAILS (NEW TABLES)
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

    //TECHNICIAN-KID RELATION
    //TECHNICIAN DOESN'T NEED A SEPARATE TABLE FROM USERS BECAUSE, UNLIKE THE DRIVERS, IT DOESN'T HAVE ANY SPECIFIC ATTRIBUTES
    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class)->withPivot('priority')->withTimestamps();
    }

    //TODO: CHECK IF THIS IS CORRECT
    public function ordersTechnician(): HasMany
    {
        return $this->hasMany(Order::class, 'technician_id');
    }

    public function ordersManager(): HasMany
    {
        return $this->hasMany(Order::class, 'manager_id');
    }
}
