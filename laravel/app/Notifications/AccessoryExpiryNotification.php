<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class AccessoryExpiryNotification extends Notification
{
    use Queueable;

    protected $accessory;

    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct($vehicle, $accessory)
    {
        $this->accessory = $accessory;
        $this->vehicle = $vehicle;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomDbChannel::class];        //can include 'mail' after mail system is ready and show pages are built
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Acessório prestes a expirar.')
                    ->action('Ver acessório em', route('vehicleAccessories.show', ['vehicleAccessory' => $this->accessory->id]))
                    ->line('O acessório com id ' . $this->accessory->id . ' do veículo ' . $this->vehicle->id . ' está prestes a expirar.');
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
            'related_entity_type' => Vehicle::class,
            'related_entity_id' => $this->vehicle->id,
            'type' => 'Acessório',
            'title' => 'Acessório perto de expirar',
            'message' => 'O acessório com id ' . $this->accessory->id . ' do veículo ' . $this->vehicle->id . ' está prestes a expirar.',
            'is_read' => false,
        ];
    }
}
