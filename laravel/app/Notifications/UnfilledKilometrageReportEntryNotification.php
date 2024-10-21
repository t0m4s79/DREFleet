<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class UnfilledKilometrageReportEntryNotification extends Notification
{
    use Queueable;

    protected $missingDate;

    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct($vehicle, $missingDate)
    {
        $this->missingDate = $missingDate;
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
                    ->line('Entrada de relatório de kilometragem em falta.')
                    ->action('Ver detalhes em', route('vehicles.kilometrageReports', ['vehicle' => $this->vehicle->id]))
                    ->line('No mês passado, uma entrada do relatório de kilometragem não foi preenchida na data ' . $this->missingDate . ' para o veículo ' . $this->vehicle->id . '.');
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
            'type' => 'Registo de Kilometragem',
            'title' => 'Entrada de relatório de kilometragem em falta',
            'message' => 'No mês passado, uma entrada do relatório de kilometragem não foi preenchida na data ' . $this->missingDate . 'para o veículo ' . $this->vehicle->id . '.',
            'is_read' => false,
        ];
    }
}
