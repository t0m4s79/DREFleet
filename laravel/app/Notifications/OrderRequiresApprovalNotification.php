<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\CustomDbChannel;
use Illuminate\Notifications\Messages\MailMessage;

class OrderRequiresApprovalNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [CustomDbChannel::class];                //can include 'mail' after mail system is ready and show pages are built
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expected_begin_date = \Carbon\Carbon::parse($this->order->expected_begin_date)->format('d-m-Y H:i');

        return (new MailMessage)
                    ->line('Pedido necessita de aprovação.')
                    ->action('Ver pedido em', route('orders.edit'))
                    ->line('O pedido com id ' . $this->order->id . ' com data de início breve, marcada para ' . $expected_begin_date . ', necessita de aprovação.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $expected_begin_date = \Carbon\Carbon::parse($this->order->expected_begin_date)->format('d-m-Y H:i');

        return [
            'user_id' => $notifiable->id,
            'related_entity_type' => Order::class,
            'related_entity_id' => $this->order->id,
            'type' => 'Pedido',
            'title' => 'Aprovação de Pedido',
            'message' => 'O pedido com id ' . $this->order->id . ' com data de início breve, marcada para ' . $expected_begin_date . ', necessita de aprovação.',
            'is_read' => false,
        ];
    }
}
