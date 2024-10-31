<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Jobs\SendDriverTccExpiryNotification;
use App\Notifications\DriverTccExpiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendDriverTccExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_for_driver_license_expiring()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $driver = Driver::factory()->create([
            'tcc_expiration_date' => now()->addWeek(),
        ]);

        // Dispatch the job
        (new SendDriverTccExpiryNotification())->handle();

        // Assert that the user was notified
        Notification::assertSentTo(
            [$user],
            \App\Notifications\DriverTccExpiryNotification::class
        );

        // Assert that the driver was notified
        Notification::assertSentTo(
            [User::find($driver->user_id)],
            \App\Notifications\DriverTccExpiryNotification::class
        );
    }

    public function test_notification_not_sent_for_driver_license_not_expiring()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        User::factory()->create(['user_type' => 'Gestor']);
        Driver::factory()->create([
            'tcc_expiration_date' => now()->addMonths(2),
        ]);

        // Dispatch the job
        (new SendDriverTccExpiryNotification())->handle();

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }
}
