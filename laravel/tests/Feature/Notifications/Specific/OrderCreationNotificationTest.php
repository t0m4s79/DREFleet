<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use App\Notifications\OrderCreationNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user and order
        $user = User::factory()->create();
        $order = Order::factory()->create();

        // Send notification
        $notification = new OrderCreationNotification($order);
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

        // Create an instance of the notification
        $notification = new OrderCreationNotification($order);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage); // Ensure the message is of type MailMessage
        $this->assertStringContainsString('Novo pedido.', $mailMessage->introLines[0]); // Verify intro line

        // Verify the action button URL contains the correct order id
        $this->assertStringContainsString(route('orders.edit', ['order' => $order->id]), $mailMessage->actionUrl);

        // Verify the outro line contains the correct information about the order
        $expectedBeginDate = Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i');
        $this->assertStringContainsString('Foi criado um novo pedido com id ' . $order->id . ' com data de início marcada para ' . $expectedBeginDate, $mailMessage->outroLines[0]);
    }
}