<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\OrderOccurrence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use App\Notifications\OrderOccurrenceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

class OrderOccurrenceNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and kilometrage report
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $occurrence = OrderOccurrence::factory()->create(['order_id' => $order->id]);

        // Send notification
        $notification = new OrderOccurrenceNotification($order, $occurrence);
        $user->notify($notification);

        // Check if the notification is stored in the database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $order->id,
            'related_entity_type' => Order::class,
            'type' => 'Pedido',
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        $order = Order::factory()->create();
        $occurrence = OrderOccurrence::factory()->create(['order_id' => $order->id]);

        // Create an instance of the notification
        $notification = new OrderOccurrenceNotification($order, $occurrence);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Nova ocorrência.', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('orders.occurrences', ['order' => $order->id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('Uma nova ocorrênica com id ' . $occurrence->id . ' do pedido ' . $order->id . ' foi reportada.', $mailMessage->outroLines[0]);
    }
}
