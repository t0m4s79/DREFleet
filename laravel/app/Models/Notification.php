<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends Model         //DatabaseNotification?
{
    use HasFactory;

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'related_entity_type',
        'related_entity_id',
        'type',
        'title',
        'message',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related entity (vehicle, driver, order, etc.) for the notification.
     */
    public function relatedEntity()
    {
        return $this->morphTo();
    }
}