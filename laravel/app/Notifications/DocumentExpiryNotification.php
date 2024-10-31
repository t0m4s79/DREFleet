<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentExpiryNotification extends Notification
{
    use Queueable;

    protected $document;

    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct($vehicle, $document)
    {
        $this->document = $document;
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
                    ->line('Documento prestes a expirar.')
                    ->action('Ver detalhes do documento em ', route('vehicleDocuments.edit', ['vehicleDocument' => $this->document->id]))
                    ->line('O documento com id ' . $this->document->id . ' do veículo ' . $this->vehicle->id . ' está prestes a expirar.');
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
            'type' => 'Documento',
            'title' => 'Documento perto de expirar',
            'message' => 'O documento com id ' . $this->document->id . ' do veículo ' . $this->vehicle->id . ' está prestes a expirar.',
            'is_read' => false,
        ];
    }
}
