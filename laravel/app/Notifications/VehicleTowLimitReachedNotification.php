<?php

namespace App\Notifications;

use App\Models\Vehicle;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VehicleTowLimitReachedNotification extends Notification
{
    use Queueable;

    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct($vehicle)
    {
        $this->vehicle = $vehicle;
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
                    ->line('Limite de reboques do veículo atingido')
                    ->action('Ver detalhes do veículo em ', route('vehicles.edit', ['vehicle' => $this->vehicle->id]))
                    ->line('O limite de reboques do veículo com id ' . $this->vehicle->id . ' foi atingido (' . $this->vehicle->yearly_allowed_tows . ' reboques)');
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
            'type' => 'Veículo',
            'title' => 'Limite de reboques do veículo atingido',
            'message' => 'O limite de reboques do veículo com id ' . $this->vehicle->id . ' foi atingido (' . $this->vehicle->yearly_allowed_tows . ' reboques)',
            'is_read' => false,
        ];
    }
}
