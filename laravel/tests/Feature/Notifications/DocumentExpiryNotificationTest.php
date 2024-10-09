<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DocumentExpiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;
    
    // public function test_toMail_sends_correct_notification()
    // {
    //     // Fake email sending
    //     Mail::fake();

    //     // Create a user, vehicle, and document
    //     $user = User::factory()->create();
    //     $vehicle = Vehicle::factory()->create();
    //     $document = VehicleDocument::factory()->create(['vehicle_id' => $vehicle->id]);

    //     // Send notification
    //     $notification = new DocumentExpiryNotification($vehicle, $document);
    //     $user->notify($notification);

    //     // Assert an email was sent
    //     Mail::assertSent(function ($mail) use ($notification, $user, $vehicle, $document) {
    //         return $mail->hasTo($user->email) &&
    //             $mail->actionUrl === route('vehicle.documents.show', ['vehicle' => $vehicle->id, 'document' => $document->id]) &&
    //             str_contains($mail->introLines[0], 'Documento prestes a expirar.');
    //     });
    // }

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and document
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $document = VehicleDocument::factory()->create(['vehicle_id' => $vehicle->id]);

        // Send notification
        $notification = new DocumentExpiryNotification($vehicle, $document);

        //dd($notification->toArray($user)); // Pass the notifiable user here
        
        $user->notify($notification);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $vehicle->id,
            'related_entity_type' => Vehicle::class,
            'type' => 'Documento'
        ]);
    }
}
