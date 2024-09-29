<?php

namespace Tests\Feature;

use Log;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\OrderRoute;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_driver_has_many_orders(): void
    {
        $driver = Driver::factory()->create();

        $order = Order::factory()->create([
            'driver_id' => $driver->user_id,
        ]);

        $this->assertTrue($driver->orders->contains($order)); // Check if the driver's orders include this order
    }

    public function test_driver_has_many_order_routes(): void
    {
        $driver = Driver::factory()->create();

        $orderRoutes = OrderRoute::factory()->count(3)->create();

        $driver->orderRoutes()->attach($orderRoutes->pluck('id'));

        $this->assertCount(3, $driver->orderRoutes);

        foreach ($orderRoutes as $orderRoute) {
            $this->assertTrue($driver->orderRoutes->contains($orderRoute));
        }
    }

    public function test_drivers_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/drivers');

        $response->assertOk();
    }

    public function test_driver_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/drivers/create');

        $response->assertOk();
    }

    public function test_driver_edit_page_is_displayed(): void
    {
        $driver = Driver::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/drivers/edit/{$driver->user_id}");

        $response->assertOk();
    }

    public function test_user_can_create_a_driver(): void
    {
        $heavyLicense = fake()->boolean();
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $driverData = [
            'user_id' => User::factory()->create()->id,
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $driverData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');


        $this->assertDatabaseHas('drivers', $driverData);
    }

    public function test_create_driver_fails_on_non_none_user_type(): void
    {
        $user = User::factory()->create([
            'user_type' => Arr::random(['Técnico', 'Gestor', 'Condutor', 'Administrador']),
        ]);

        $driverData = [
            'user_id' => $user->id,
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $driverData);

        $response->assertSessionHasErrors(['user_id']);

        $this->assertDatabaseMissing('drivers', $driverData);
    }

    public function test_user_can_edit_a_driver(): void
    {
        $heavyLicense = rand(0,1);
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $driver = Driver::factory()->create([
            'user_id' => User::factory()->create()->id,
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType,
        ]);
    
        $newHeavyLicense = rand(0,1);
        $newHeavyLicenseType = $newHeavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        $updatedData = [
            'user_id' => $driver->user_id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
            'heavy_license' => $newHeavyLicense,
            'heavy_license_type' => $newHeavyLicenseType,
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/drivers/edit/{$driver->user_id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $this->assertDatabaseHas('drivers', [
            'user_id' => $driver->user_id,
            'heavy_license' => $newHeavyLicense,
            'heavy_license_type' => $newHeavyLicenseType
        ]);
    }

    public function test_user_can_delete_a_driver(): void
    {
        $driver = Driver::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/drivers/delete/{$driver->user_id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driver->user_id,
        ]);
    }

    public function test_driver_creation_handles_exception()
    {
        $incomingFields = [
            'user_id' => User::factory()->create()->id,
            'heavy_license' => '0',
        ];

        // Mock the Driver model to throw an exception
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
