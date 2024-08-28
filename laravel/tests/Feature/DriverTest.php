<?php

namespace Tests\Feature;

use Log;
use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_drivers_page_is_displayed(): void              //USER_ID ALWAYS 0 ON DRIVER FACTORY CREATION????
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/drivers');

        $response->assertOk();
    }

    public function test_drivers_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/drivers/create');

        $response->assertOk();
    }

    public function test_driver_edit_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $driver = Driver::factory()->create([
            'heavy_license' => '1',
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get("/drivers/edit/{$user->id}");

        $response->assertOk();
    }

    public function test_user_can_create_a_driver(): void
    {
        $user = User::factory()->create();

        $driverData = [
            'user_id' => $user->id,
            'heavy_license' => '1',
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $driverData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');


        $this->assertDatabaseHas('drivers', $driverData);
    }

    public function test_user_can_edit_a_driver(): void
    {
        $user = User::factory()->create();

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'heavy_license' => '1',
        ]);
    
        $updatedData = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => '1',
            'heavy_license' => 'Não',
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/drivers/edit/{$user->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $this->assertDatabaseHas('drivers', [
            'user_id' => $user->id,
            'heavy_license' => '0',
        ]);
    }

    public function test_user_can_delete_a_driver(): void
    {
        $user = User::factory()->create();

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/drivers/delete/{$user->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $user->id,
        ]);
    }

    public function test_driver_creation_handles_exception()
    {
        $user = User::factory()->create();

        $incomingFields = [
            'user_id' => $user->id,
            'heavy_license' => '0',
        ];

        // Mock the Vehicle model to throw an exception
        $this->mock(Driver::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create driver route
        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/drivers'); // Ensure it redirects back to the form
    }
}
