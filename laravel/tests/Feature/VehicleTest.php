<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_vehicles_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/vehicles');

        $response->assertOk();
    }

    public function test_vehicle_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/vehicles/create');

        $response->assertOk();
    }

    public function test_vehicle_edit_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/vehicles/edit/{$vehicle->id}");

        $response->assertOk();
    }



    public function test_user_can_create_a_vehicle(): void
    {
        $vehicleData = [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'license_plate' => '11XX11',
            'heavy_vehicle' => '1',
            'wheelchair_adapted' => '1',
            'capacity' => '9',
            'fuel_consumption' => '8.23',
            'status' => 'Disponível',
            'current_month_fuel_requests' => '2',
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/vehicles/create', $vehicleData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/vehicles');


        $this->assertDatabaseHas('vehicles', $vehicleData);
    }

    public function test_user_can_edit_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'license_plate' => '11XX11',
            'heavy_vehicle' => '1',
            'wheelchair_adapted' => '1',
            'capacity' => '9',
            'fuel_consumption' => '8.23',
            'status' => 'Disponível',
            'current_month_fuel_requests' => '2',
        ]);
    
        $updatedData = [
            'make' => 'Peugeot',
            'model' => '106',
            'license_plate' => '33YY11',
            'heavy_vehicle' => '0',
            'wheelchair_adapted' => '0',
            'capacity' => '5',
            'fuel_consumption' => '10.00',
            'status' => 'Indisponível',
            'current_month_fuel_requests' => '1',
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/vehicles/edit/{$vehicle->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'make' => 'Peugeot',
            'model' => '106',
            'license_plate' => '33YY11',
            'heavy_vehicle' => '0',
            'wheelchair_adapted' => '0',
            'capacity' => '5',
            'fuel_consumption' => '10.00',
            'status' => 'Indisponível',
            'current_month_fuel_requests' => '1',
        ]);
        
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'make' => 'Peugeot',
            'model' => '106',
            'license_plate' => '33YY11',
            'heavy_vehicle' => '0',
            'wheelchair_adapted' => '0',
            'capacity' => '5',
            'fuel_consumption' => '10.00',
            'status' => 'Indisponível',
            'current_month_fuel_requests' => '1',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/vehicles/delete/{$vehicle->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/vehicles');

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {        
        // Prepare the incoming fields
        $incomingFields = [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'license_plate' => 'XYZ123',
            'heavy_vehicle' => 0,
            'wheelchair_adapted' => 0,
            'capacity' => 4,
            'fuel_consumption' => 8.5,
            'status' => 'active',
            'current_month_fuel_requests' => 2,
        ];

        // Mock the Vehicle model to throw an exception
        $this->mock(Vehicle::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle route
        $response = $this
            ->actingAs($this->user)
            ->post('/vehicles/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(); // Ensure it redirects back to the form
        $response->assertSessionHas('error', 'Houve um problema ao criar o veículo. Tente novamente.');
    }
}
