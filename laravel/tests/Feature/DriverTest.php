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

    /*
        Aveiro - AV.
        Beja - BE.
        Braga - BR.
        Bragança - BG.
        Castelo Branco - CB.
        Coimbra - C.
        Évora - E.
        Faro - FA.
        Guarda - GD.
        Leiria - LE.
        Lisboa - L.
        Portalegre - PT.
        Porto - P.
        Santarém - SA.
        Setúbal - SE.
        Viana do Castelo - VC.
        Vila Real - VR.
        Viseu - VS.
        Angra do Heroísmo - AN.
        Horta - H.
        Ponta Delgada - A.
        Funchal - M.
    */
    protected function getRandomRegionIdentifier() :string
    {
        return Arr::random(['AV','BE','BR','BG','CB','C','E','FA','GD','LE','L','PT','P','SA','SE','VC','VR','VS','AN','H','A','M']);
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
            'license_region_identifier' => $this->getRandomRegionIdentifier(),
            'license_middle_digits' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'license_last_digit' => rand(0, 9),
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $driverData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $expectedLicenseNumber = $driverData['license_region_identifier'] . '-' . $driverData['license_middle_digits'] . ' ' . $driverData['license_last_digit'];

        $this->assertDatabaseHas('drivers', [
            'user_id' => $driverData['user_id'],
            'license_number' => $expectedLicenseNumber,
            'heavy_license' => $driverData['heavy_license'],
            'heavy_license_type' => $driverData['heavy_license_type'],
        ]);
    }

    public function test_create_driver_fails_on_non_none_user_type(): void
    {
        $user = User::factory()->create([
            'user_type' => Arr::random(['Técnico', 'Gestor', 'Condutor', 'Administrador']),
        ]);

        $driverData = [
            'user_id' => $user->id,
            'license_region_identifier' => $this->getRandomRegionIdentifier(),
            'license_middle_digits' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'license_last_digit' => rand(0, 9),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/drivers/create', $driverData);

        $response->assertSessionHasErrors(['user_id']);

        $expectedLicenseNumber = $driverData['license_region_identifier'] . '-' . $driverData['license_middle_digits'] . ' ' . $driverData['license_last_digit'];

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData['user_id'],
            'license_number' => $expectedLicenseNumber,
            'heavy_license' => $driverData['heavy_license'],
            'heavy_license_type' => $driverData['heavy_license_type'],
        ]);
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
            'license_region_identifier' => $this->getRandomRegionIdentifier(),
            'license_middle_digits' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'license_last_digit' => rand(0, 9),
            'heavy_license' => $newHeavyLicense,
            'heavy_license_type' => $newHeavyLicenseType,
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put("/drivers/edit/{$driver->user_id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/drivers');

        $expectedLicenseNumber = $updatedData['license_region_identifier'] . '-' . $updatedData['license_middle_digits'] . ' ' . $updatedData['license_last_digit'];


        $this->assertDatabaseHas('drivers', [
            'user_id' => $driver->user_id,
            'license_number' => $expectedLicenseNumber,
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
            'license_region_identifier' => $this->getRandomRegionIdentifier(),
            'license_middle_digits' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'license_last_digit' => rand(0, 9),
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
