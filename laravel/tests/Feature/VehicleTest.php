<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
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

    public function test_vehicle_has_many_orders(): void
    {
        $vehicle = Vehicle::factory()->create();

        $orders = Order::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $this->assertCount(3, $vehicle->orders);

        foreach ($orders as $order) {
            $this->assertTrue($vehicle->orders->contains($order));
        }
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
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $vehicleData = [
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
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
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $vehicle = Vehicle::factory()->create([
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
        ]);

        $newHeavyVehicle = fake()->boolean();
        $newHeavyType = $newHeavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $updatedData = [
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $newHeavyVehicle,
            'heavy_type' => $newHeavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put("/vehicles/edit/{$vehicle->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', $updatedData);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

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
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        // Prepare the incoming fields
        $incomingFields = [
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
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
        $response->assertRedirect('/vehicles');
    }
}
