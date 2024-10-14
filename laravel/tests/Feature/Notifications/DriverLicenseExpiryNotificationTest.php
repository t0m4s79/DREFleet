<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
