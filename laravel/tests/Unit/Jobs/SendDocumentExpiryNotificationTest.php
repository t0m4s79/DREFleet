<?php

namespace Tests\Feature;

use App\Http\Controllers\VehicleDocumentController;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Jobs\SendDocumentExpiryNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendDocumentExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_for_expiring_vehicle_documents()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();

        // Delete documents created on vehicle factory
        $controller = new VehicleDocumentController();

        foreach ($vehicle->documents as $document) {
            $controller->deleteVehicleDocument($document->id);
        }

        VehicleDocument::factory()->create([
            'vehicle_id' => $vehicle->id,
            'expiration_date' => now()->addWeek(), // within the next month
        ]);

        // Dispatch the job
        (new SendDocumentExpiryNotification())->handle();

        // Assert that the user was notified
        Notification::assertSentTo(
            [$user],
            \App\Notifications\DocumentExpiryNotification::class
        );
    }

    public function test_notification_not_sent_for_not_expiring_vehicle_documents()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();

        // Delete documents created on vehicle factory
        $controller = new VehicleDocumentController();

        foreach ($vehicle->documents as $document) {
            $controller->deleteVehicleDocument($document->id);
        }

        VehicleDocument::factory()->create([
            'vehicle_id' => $vehicle->id,
            'expiration_date' => now()->addMonths(2), // beyond one month
        ]);

        // Dispatch the job
        (new SendDocumentExpiryNotification())->handle();

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }
}
