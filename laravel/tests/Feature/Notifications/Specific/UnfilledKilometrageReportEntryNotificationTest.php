<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleKilometrageReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\UnfilledKilometrageReportEntryNotification;

class UnfilledKilometrageReportEntryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_toArray_stores_notification_correctly()
    {
        // Create a user, vehicle, and kilometrage report
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        
        // Define a missing date (relevant for the notification)
        $missingDate = now()->subMonth()->startOfMonth()->format('Y-m-d');

        // Make sure there are no reports or dates might coincide making the test fail
        VehicleKilometrageReport::where('vehicle_id', $vehicle->id)->delete();

        // Send notification
        $notification = new UnfilledKilometrageReportEntryNotification($vehicle, $missingDate);
        $user->notify($notification);

        // Check if the notification is stored in the database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_entity_id' => $vehicle->id,
            'related_entity_type' => Vehicle::class,
            'type' => 'Relatório de Kilometragem'
        ]);
    }
}
