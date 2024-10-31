<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class VehicleRefuelRequestNotification extends Notification
{
    use Queueable;

    protected $request;
    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct($request, $vehicle)
    {
        $this->request = $request;
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
                    ->line('Novo pedido de reabastecimento do tipo ' . $this->request->request_type . '.')
                    ->action('Ver detalhes do veículo em ', route('vehicles.refuelRequests', ['vehicle' => $this->vehicle->id]))
                    ->line('Foi criado um novo pedido de reabastecimento (' . $this->vehicle->current_month_fuel_requests . 'º este mês) com id ' . $this->request->id . ' do tipo ' . $this->request->request_type . ' para o veículo ' . $this->vehicle->id . '.');
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
            'type' => 'Pedido de Reabastecimento',
            'title' => 'Novo pedido de reabastecimento do tipo ' . $this->request->request_type,
            'message' => 'Foi criado um novo pedido de reabastecimento (' . $this->vehicle->current_month_fuel_requests . 'º este mês) com id ' . $this->request->id . ' do tipo ' . $this->request->request_type . ' para o veículo ' . $this->vehicle->id . '.',
            'is_read' => false,
        ];
    }
}
