<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DriverLicenseExpiryNotification;

class DriverLicenseExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user and order
        $user = User::factory()->create();
        $driver = Driver::factory()->create();

        // Send notification
        $notification = new DriverLicenseExpiryNotification($driver);        
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $driver->user_id,
            'related_entity_type' => Driver::class,
            'type' => 'Condutor'
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        $driver = Driver::factory()->create();

        // Create an instance of the notification
        $notification = new DriverLicenseExpiryNotification($driver);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Carta de condução prestes a expirar.', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('drivers.edit', ['driver' => $driver->user_id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('A carta de condução do condutor com id ' . $driver->user_id . ' está prestes a expirar.', $mailMessage->outroLines[0]);
    }
}
