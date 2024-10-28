<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRefuelRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\VehicleRefuelRequestNotification;

class VehicleRefuelRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and kilometrage report
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $request = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        // Send notification
        $notification = new VehicleRefuelRequestNotification($request, $vehicle);
        $user->notify($notification);

        // Check if the notification is stored in the database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $vehicle->id,
            'related_entity_type' => Vehicle::class,
            'type' => 'Pedido de Reabastecimento',
        ]);
    }

    public function test_toMail_sends_correct_notification()
    {
        $vehicle = Vehicle::factory()->create();
        $request = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        // Create an instance of the notification
        $notification = new VehicleRefuelRequestNotification($request, $vehicle);

        // Simulate a notifiable (could be a user or an anonymous notifiable)
        $notifiable = new AnonymousNotifiable();

        // Act: Call the toMail method
        $mailMessage = $notification->toMail($notifiable);

        // Assert: Verify that the mail message is properly structured
        $this->assertInstanceOf(MailMessage::class, $mailMessage); // Ensure the message is of type MailMessage
        
        $this->assertEquals(
            'Novo pedido de reabastecimento do tipo ' . $request->request_type . '.',
            $mailMessage->introLines[0]
        );
        
        // Verify the action button URL matches the expected route
        $this->assertEquals(
            route('vehicles.refuelRequests', ['vehicle' => $vehicle->id]),
            $mailMessage->actionUrl
        );
        
        // Verify the outro line matches exactly
        $this->assertEquals(
            'Foi criado um novo pedido de reabastecimento com id ' . $request->id . ' do tipo ' . $request->request_type . ' para o veículo ' . $vehicle->id . '.',
            $mailMessage->outroLines[0]
        );
    }
}