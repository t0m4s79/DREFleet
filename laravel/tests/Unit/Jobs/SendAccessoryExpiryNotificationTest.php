<?php

namespace Tests\Feature;

use App\Http\Controllers\VehicleAccessoryController;
use App\Jobs\SendAccesssoryExpiryNotification;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAccessory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendAccessoryExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_for_expiring_vehicle_documents()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();
        
        $controller = new VehicleAccessoryController;

        foreach ($vehicle->vehicleAccessories as $accessory) {
            $controller->deleteVehicleAccessory($accessory->id);
        }
        
        VehicleAccessory::factory()->create([
            'vehicle_id' => $vehicle->id,
            'expiration_date' => now()->addWeek(), // within the next month
        ]);

        // Dispatch the job
        (new SendAccesssoryExpiryNotification())->handle();

        // Assert that the user was notified
        Notification::assertSentTo(
            [$user],
            \App\Notifications\AccessoryExpiryNotification::class
        );
    }

    public function test_notification_not_sent_for_not_expiring_vehicle_documents()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();

        $controller = new VehicleAccessoryController;

        foreach ($vehicle->vehicleAccessories as $accessory) {
            $controller->deleteVehicleAccessory($accessory->id);
        }

        VehicleAccessory::factory()->create([
            'vehicle_id' => $vehicle->id,
            'expiration_date' => now()->addMonths(2), // beyond one month
        ]);

        // Dispatch the job
        (new SendAccesssoryExpiryNotification())->handle();

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }
}
