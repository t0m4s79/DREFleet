<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class OrderOccurrenceNotification extends Notification
{
    use Queueable;

    protected $order;

    protected $vehicle;

    protected $occurrence;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $occurrence)
    {
        $this->order = $order;
        $this->occurrence = $occurrence;

        $this->vehicle = Vehicle::where('id', $order->vehicle_id)->withCount([
            'orders as this_year_tow_counts' => function ($query) {
                $query->whereYear('expected_begin_date', now()->year)
                      ->whereHas('occurrences', function ($query) {
                          $query->where('vehicle_towed', 1);
                      });
            }
        ])->first();

        if ((int) $this->vehicle->this_year_tow_counts == $this->vehicle->yearly_allowed_tows) {
            foreach (User::where('user_type', 'Gestor')->get() as $user) {
                $user->notify(new VehicleTowLimitReachedNotification($this->vehicle));
            } 
        }
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
        $message = 'Uma nova ocorrênica com id ' . $this->occurrence->id . ' do pedido ' . $this->order->id . ' foi reportada.';

        if ($this->occurrence->vehicle_towed == 1 && $this->vehicle->this_year_tow_counts > 0 && $this->order->expected_begin_date->year == Carbon::now()->year) {
            $message .= ' O veículo com id ' . $this->vehicle->id . ' foi rebocado (' . $this->vehicle->this_year_tow_counts . 'ª vez de ' . $this->vehicle->yearly_allowed_tows . ' permitidas)';
        }

        return (new MailMessage)
                    ->line('Nova ocorrência.')
                    ->action('Ver detalhes da ocorrência em ', route('orders.occurrences', ['order' => $this->order->id]))
                    ->line($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $date = Carbon::parse($this->order->expected_begin_date)->format('d-m-Y');
        $time = Carbon::parse($this->order->expected_begin_date)->format('H:');

        $message = 'Uma nova ocorrênica com id ' . $this->occurrence->id . ' do pedido ' . $this->order->id . ' foi reportada no dia ' . $date . '.';

        if ($this->occurrence->vehicle_towed == 1 && $this->vehicle->this_year_tow_counts > 0 && $this->order->expected_begin_date->year == Carbon::now()->year) {
            $message .= ' O veículo com id ' . $this->vehicle->id . ' foi rebocado (' . $this->vehicle->this_year_tow_counts . 'ª vez de ' . $this->vehicle->yearly_allowed_tows . ' permitidas)';
        }

        return [
            'user_id' => $notifiable->id,
            'related_entity_type' => Order::class,
            'related_entity_id' => $this->order->id,
            'type' => 'Ocorrência',
            'title' => 'Nova ocorrência',
            'message' => $message,
            'is_read' => false,
        ];
    }

    public function getOccurrence()
    {
        return $this->occurrence;
    }
}
