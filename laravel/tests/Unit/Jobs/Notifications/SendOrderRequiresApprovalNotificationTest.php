<?php

namespace Tests\Feature;

use App\Jobs\SendOrderRequiresApprovalNotification;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendOrderRequiresApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_for_order_requiring_approval()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        Order::factory()->create([
            'manager_id' => null,
            'approved_date' => null,
            'expected_begin_date' => now()->addWeek(),
        ]);

        // Dispatch the job
        (new SendOrderRequiresApprovalNotification())->handle();

        // Assert that the user was notified
        Notification::assertSentTo(
            [$user],
            \App\Notifications\OrderRequiresApprovalNotification::class
        );
    }

    public function test_notification_not_sent_for_orders_not_requiring_approval()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        Order::factory()->create([
            'manager_id' => null,
            'approved_date' => null,
            'expected_begin_date' => now()->addMonths(2),
        ]);

        Order::factory()->create([
            'manager_id' => null,
            'approved_date' => now()->now(),
            'expected_begin_date' => now()->addMonths(2),
        ]);

        Order::factory()->create([
            'manager_id' => $user->id,
            'approved_date' => null,
            'expected_begin_date' => now()->addMonths(2),
        ]);

        // Dispatch the job
        (new SendOrderRequiresApprovalNotification())->handle();

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }
}
