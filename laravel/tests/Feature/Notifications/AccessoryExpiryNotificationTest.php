<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAccessory;
use App\Notifications\AccessoryExpiryNotification;
use App\Notifications\DocumentExpiryNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
}
