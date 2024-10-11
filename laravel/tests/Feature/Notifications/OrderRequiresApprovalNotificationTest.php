<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\OrderRequiresApprovalNotification;

class OrderRequiresApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and document
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
}
