<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'related_entity_type',
        'related_entity_id',
        'type',
        'title',
        'message',
        'is_read',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
    }

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

    protected static function boot()
    {
        parent::boot();

        // Generate a UUID when creating a new Notification
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}