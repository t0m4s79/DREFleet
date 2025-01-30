<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\VehicleRefuelRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleRefuelRequestTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }

    public function test_refuel_request_belongs_to_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $request = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $this->assertTrue($request->vehicle->is($vehicle));
    }

    public function test_vehicle_refuel_request_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleRefuelRequests.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_refuel_request_edit_page_is_displayed(): void
    {
        $request = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleRefuelRequests.showEdit', $request->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_refuel_request(): void
    {        
        $requestData = [
            'date' => fake()->date(),
            'quantity' => fake()->randomFloat(3, 1, 80),
            'cost_per_unit' => fake()->randomFloat(3, 0.5, 3.0),
            'total_cost' => fake()->randomFloat(2, 5, 50),
            'kilometrage' => rand(10000,200000),
            'fuel_type' => Arr::random(['Gasóleo','Gasolina 95','Gasolina 98','Elétrico']),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleRefuelRequests.create'), $requestData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.refuelRequests', $requestData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_refuel_requests', $requestData);
    }

    public function test_user_can_edit_a_vehicle_refuel_request(): void
    {
        $request = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $updatedData = [
            'date' => fake()->date(),
            'quantity' => fake()->randomFloat(3, 1, 80),
            'cost_per_unit' => fake()->randomFloat(3, 0.5, 3.0),
            'total_cost' => fake()->randomFloat(2, 5, 50),
            'kilometrage' => rand(10000,200000),
            'fuel_type' => Arr::random(['Gasóleo','Gasolina 95','Gasolina 98','Elétrico']),
            'request_type' => Arr::random(['Normal','Especial', 'Excepcional']),
            'monthly_request_number' => rand(1, 15),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleRefuelRequests.edit', $request->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.refuelRequests', $updatedData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_refuel_requests', $updatedData);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicleDocument = VehicleRefuelRequest::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicleRefuelRequests.delete', $vehicleDocument->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.refuelRequests', $vehicleDocument->vehicle->id));

        $this->assertDatabaseMissing('vehicle_refuel_requests', [
            'id' => $vehicleDocument->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        $data = [
            'date' => fake()->date(),
            'quantity' => fake()->randomFloat(3, 1, 80),
            'cost_per_unit' => fake()->randomFloat(3, 0.5, 3.0),
            'total_cost' => fake()->randomFloat(2, 5, 50),
            'kilometrage' => rand(10000,200000),
            'fuel_type' => Arr::random(['Gasóleo','Gasolina 95','Gasolina 98','Elétrico']),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        // Mock the Vehicle Document model to throw an exception
        $this->mock(VehicleRefuelRequest::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle document route
        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleRefuelRequests.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.refuelRequests',  $data['vehicle_id']));
    }
}