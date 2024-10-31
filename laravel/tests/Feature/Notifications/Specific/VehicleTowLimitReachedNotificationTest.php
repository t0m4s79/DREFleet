<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\VehicleTowLimitReachedNotification;

class VehicleTowLimitReachedNotificationTest extends TestCase
{
    public function test_toArray_stores_notification_correctly()
    {
        // Arrange
        $vehicle = Vehicle::factory()->create(['yearly_allowed_tows' => 3]);
        $user = User::factory()->create();

        // Act
        $notification = new VehicleTowLimitReachedNotification($vehicle);
        $arrayRepresentation = $notification->toArray($user);

        // Assert
        $this->assertEquals($user->id, $arrayRepresentation['user_id']);
        $this->assertEquals(Vehicle::class, $arrayRepresentation['related_entity_type']);
        $this->assertEquals($vehicle->id, $arrayRepresentation['related_entity_id']);
        $this->assertEquals('Veículo', $arrayRepresentation['type']);
        $this->assertEquals('Limite de reboques do veículo atingido', $arrayRepresentation['title']);
        $this->assertEquals('O limite de reboques do veículo com id ' . $vehicle->id . ' foi atingido (3 reboques)', $arrayRepresentation['message']);
        $this->assertFalse($arrayRepresentation['is_read']);
    }

    public function test_toMail_sends_correct_notification()
    {

        // Arrange: Create a vehicle and accessory
        $vehicle = Vehicle::factory()->create();

        // Create an instance of the notification
        $notification = new VehicleTowLimitReachedNotification($vehicle);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertStringContainsString('Limite de reboques do veículo atingido', $mailMessage->introLines[0]);
        $this->assertStringContainsString(route('vehicles.edit', ['vehicle' => $vehicle->id]), $mailMessage->actionUrl);
        $this->assertStringContainsString('O limite de reboques do veículo com id ' . $vehicle->id . ' foi atingido (' . $vehicle->yearly_allowed_tows . ' reboques)', $mailMessage->outroLines[0]);
    }
}
