<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\VehicleAccessory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleAccessoryTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_accessory_belongs_to_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $vehicleAccessory = VehicleAccessory::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->assertTrue($vehicleAccessory->vehicle->is($vehicle));
    }

    public function test_vehicle_accessories_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleAccessories.index'));

        $response->assertOk();
    }

    public function test_vehicle_accessory_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleAccessories.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_accessory_edit_page_is_displayed(): void
    {
        $vehicleAccessory = VehicleAccessory::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleAccessories.showEdit', $vehicleAccessory->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_accessory(): void
    {
        $expirationDate = fake()->dateTime()->format('Y-m-d');
        $condition = now()->toDateTimeString() > $expirationDate ? 'Expirado' : $expirationDate;

        $vehicleAccessoryData = [
            'name' => fake()->name(),
            'condition' => $condition,
            'expiration_date' => $expirationDate,
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleAccessories.create'), $vehicleAccessoryData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicleAccessories.index'));

        $this->assertDatabaseHas('vehicle_accessories', $vehicleAccessoryData);
    }

    public function test_user_can_edit_a_vehicle_accessory(): void
    {
        $expirationDate = fake()->dateTime()->format('Y-m-d');
        $condition = now()->toDateTimeString() > $expirationDate ? 'Expirado' : $expirationDate;

        $vehicleAccessory = VehicleAccessory::factory()->create();

        $updatedData = [
            'name' => fake()->name(),
            'condition' => $condition,
            'expiration_date' => $expirationDate,
            'vehicle_id' => $vehicleAccessory->vehicle_id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleAccessories.edit', $vehicleAccessory->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicleAccessories.index'));

        $this->assertDatabaseHas('vehicle_accessories', $updatedData);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicleAccessory = VehicleAccessory::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicleAccessories.delete', $vehicleAccessory->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicleAccessories.index'));

        $this->assertDatabaseMissing('vehicle_accessories', [
            'id' => $vehicleAccessory->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        // Prepare the incoming fields
        $data = [
            'name' => fake()->name(),
            'condition' => Arr::random(['Expirado', 'Danificado', 'Aceitável']),
            'expiration_date' => fake()->dateTime()->format('Y-m-d'),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        // Mock the Vehicle Accessory model to throw an exception
        $this->mock(VehicleAccessory::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle accessory route
        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleAccessories.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicleAccessories.index'));
    }
}
