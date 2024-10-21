<?php

namespace App\Notifications;

use App\Models\Order;
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

    protected $occurrence;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $occurrence)
    {
        $this->occurrence = $occurrence;
        $this->order = $order;
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
                    ->line('Nova ocorrência.')
                    ->action('Ver detalhes da ocorrência em ', route('orders.occurrences', ['order' => $this->order->id]))
                    ->line('Uma nova ocorrênica com id ' . $this->occurrence->id . ' do pedido ' . $this->order->id . ' foi reportada.');
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


        return [
            'user_id' => $notifiable->id,
            'related_entity_type' => Order::class,
            'related_entity_id' => $this->order->id,
            'type' => 'Ocorrência',
            'title' => 'Nova ocorrência',
            'message' => 'Uma nova ocorrênica com id ' . $this->occurrence->id . ' do pedido ' . $this->order->id . ' foi reportada no dia ' . $date . '.',
            'is_read' => false,
        ];
    }

    public function getOccurrence()
    {
        return $this->occurrence;
    }
}
