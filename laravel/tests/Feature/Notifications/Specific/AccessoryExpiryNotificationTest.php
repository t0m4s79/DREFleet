<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAccessory;
use Illuminate\Foundation\Testing\WithFaker;
use App\Notifications\DocumentExpiryNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use App\Notifications\AccessoryExpiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

class AccessoryExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle and accessory
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $document = VehicleAccessory::factory()->create(['vehicle_id' => $vehicle->id]);

        // Send notification
        $notification = new AccessoryExpiryNotification($vehicle, $document);        
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $vehicle->id,
            'related_entity_type' => Vehicle::class,
            'type' => 'Acessório'
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        $vehicle = Vehicle::factory()->create();
        $accessory = VehicleAccessory::factory()->create(['vehicle_id' => $vehicle->id]);

        // Create an instance of the notification
        $notification = new AccessoryExpiryNotification($vehicle, $accessory);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured (only part of it is ver)
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Acessório prestes a expirar.', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('vehicleAccessories.edit', ['vehicleAccessory' => $accessory->id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('O acessório com id ' . $accessory->id . ' do veículo ' . $vehicle->id . ' está prestes a expirar.', $mailMessage->outroLines[0]);
    }
}
