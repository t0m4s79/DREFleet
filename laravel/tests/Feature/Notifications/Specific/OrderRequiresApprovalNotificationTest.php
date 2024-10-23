<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\OrderRequiresApprovalNotification;

class OrderRequiresApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user and order
        $user = User::factory()->create();
        $order = Order::factory()->create();

        // Send notification
        $notification = new OrderRequiresApprovalNotification($order);        
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $order->id,
            'related_entity_type' => Order::class,
            'type' => 'Pedido'
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        $order = Order::factory()->create([
            'expected_begin_date' => now()->addDays(3), // Set a future date for testing
        ]);
        
        // Create an instance of the notification
        $notification = new OrderRequiresApprovalNotification($order);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        $expected_begin_date = \Carbon\Carbon::parse($order->expected_begin_date)->format('d-m-Y H:i');

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Pedido necessita de aprovação.', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('orders.edit', ['order' => $order->id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('O pedido com id ' . $order->id . ' com data de início marcada para ' . $expected_begin_date . ' necessita de aprovação.', $mailMessage->outroLines[0]);
    }
}
