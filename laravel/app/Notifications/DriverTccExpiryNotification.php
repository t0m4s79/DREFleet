<?php

namespace App\Notifications;

use App\Models\Driver;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverTccExpiryNotification extends Notification
{
    use Queueable;

    protected $driver;

    /**
     * Create a new notification instance.
     */
    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomDbChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Tcc prestes a expirar.')
                    ->action('Ver tcc em ', route('drivers.edit', ['driver' => $this->driver->user_id]))
                    ->line('O tcc do condutor com id ' . $this->driver->user_id . ' está prestes a expirar.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'related_entity_type' => Driver::class,
            'related_entity_id' => $this->driver->user_id,
            'type' => 'Condutor',
            'title' => 'Tcc prestes a expirar',
            'message' => 'O tcc do condutor com id ' . $this->driver->user_id . ' está prestes a expirar.',
            'is_read' => false,
        ];
    }
}
