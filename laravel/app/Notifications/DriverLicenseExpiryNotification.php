<?php

namespace App\Notifications;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class DriverLicenseExpiryNotification extends Notification
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
                    ->line('Carta de condução prestes a expirar.')
                    ->action('Ver detalhes do condutor em', route('drivers.edit', ['vehicleDocument' => $this->driver->user_id]))
                    ->line('A carta de condução do condutor com id ' . $this->driver->user . ' está prestes a expirar.');
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
            'title' => 'Carta de condução perto de expirar',
            'message' => 'A carta de condução do condutor com id ' . $this->driver->user_id . ' está prestes a expirar.',
            'is_read' => false,
        ];
    }
}
