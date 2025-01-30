<?php

namespace Tests\Feature;

use Log;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\OrderRoute;
use Illuminate\Support\Arr;
use App\Models\Notification;
use App\Models\VehicleKilometrageReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }

    protected function getRandomRegionIdentifier() :string
    {
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

    public function test_driver_has_many_kilometrage_reports(): void
    {
        $driver = Driver::factory()->create();

        $reports = VehicleKilometrageReport::factory()->count(3)->create([
            'driver_id' => $driver->user_id,
            'vehicle_id' => Vehicle::factory(),
        ]);

        foreach ($reports as $report) {
            $this->assertTrue($driver->vehicleKilometrageReports->contains($report));
        }
    }

    public function test_notifications_related_to_driver()
    {
        // User who receives notification
        $user = User::factory()->create();

        // Who the notification is about
        $driver = Driver::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'related_entity_type' => Driver::class,
            'related_entity_id' => $driver->user_id,
            'type' => 'Condutor',
            'title' => 'Driver Notification',
            'message' => 'You have a notification about the driver: ' . $driver->user_id,
            'is_read' => false,
        ]);

        $this->assertCount(1, $driver->notifications);
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_drivers_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('drivers.index'));

        $response->assertOk();
    }

    public function test_driver_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('drivers.showCreate'));

        $response->assertOk();
    }

    public function test_driver_edit_page_is_displayed(): void
    {
        $driver = Driver::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('drivers.showEdit', $driver->user_id));

        $response->assertOk();
    }

    public function test_user_can_create_a_driver(): void
    {
        $heavyLicense = fake()->boolean();
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;
        $childrenTransportationCertified = rand(0,1);
        $certificationExpiration = $childrenTransportationCertified ? fake()->date() : null;

        $driverData = [
            'user_id' => User::factory()->create()->id,
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType,
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => $childrenTransportationCertified,
            'tcc_expiration_date' => $certificationExpiration,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'user_id' => $driverData['user_id'],
            'license_number' => $driverData['license_number'],
            'heavy_license' => $driverData['heavy_license'],
            'heavy_license_type' => $driverData['heavy_license_type'],
            'license_expiration_date' => $driverData['license_expiration_date'],
            'tcc' => $driverData['tcc'],
            'tcc_expiration_date' => $driverData['tcc_expiration_date'],
        ]);
    }

    public function test_create_driver_fails_on_wrong_license_number_format (): void
    {
        // Invalid region identifier (first letters)
        $driverData_1 = [
            'user_id' => User::factory()->create()->id,
            'license_number' => 'ZZ' . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData_1);

        $response->assertSessionHasErrors(['license_number']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData_1['user_id'],
            'license_number' => $driverData_1['license_number'],
            'heavy_license' => $driverData_1['heavy_license'],
            'heavy_license_type' => $driverData_1['heavy_license_type'],
            'license_expiration_date' => $driverData_1['license_expiration_date'],
            'tcc' => $driverData_1['tcc'],
        ]);

        // Invalid middle 6 digits (input max is 5)
        $driverData_2 = [
            'user_id' => User::factory()->create()->id,
            'license_number' => $this->getRandomRegionIdentifier() . '-' . rand(0, 99999) . ' ' . rand(0, 9),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData_2);

        $response->assertSessionHasErrors(['license_number']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData_2['user_id'],
            'license_number' => $driverData_2['license_number'],
            'heavy_license' => $driverData_2['heavy_license'],
            'heavy_license_type' => $driverData_2['heavy_license_type'],
            'license_expiration_date' => $driverData_2['license_expiration_date'],
            'tcc' => $driverData_2['tcc'],
        ]);

        // Invalid last digit (input is a letter)
        $driverData_3 = [
            'user_id' => User::factory()->create()->id,
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . chr(rand(65, 90)),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData_3);

        $response->assertSessionHasErrors(['license_number']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData_3['user_id'],
            'license_number' => $driverData_3['license_number'],
            'heavy_license' => $driverData_3['heavy_license'],
            'heavy_license_type' => $driverData_3['heavy_license_type'],
            'license_expiration_date' => $driverData_3['license_expiration_date'],
            'tcc' => $driverData_3['tcc'],
        ]);

        // Invalid format altogether
        $driverData_4 = [
            'user_id' => User::factory()->create()->id,
            'license_number' => 'ZZ' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . rand(0, 9),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData_4);

        $response->assertSessionHasErrors(['license_number']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData_4['user_id'],
            'license_number' => $driverData_4['license_number'],
            'heavy_license' => $driverData_4['heavy_license'],
            'heavy_license_type' => $driverData_4['heavy_license_type'],
            'license_expiration_date' => $driverData_4['license_expiration_date'],
            'tcc' => $driverData_4['tcc'],
        ]);

        // License number already exists
        $driverData_5 = [
            'user_id' => User::factory()->create()->id,
            'license_number' => Driver::factory()->create()->license_number,
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData_5);

        $response->assertSessionHasErrors(['license_number']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData_5['user_id'],
            'license_number' => $driverData_5['license_number'],
            'heavy_license' => $driverData_5['heavy_license'],
            'heavy_license_type' => $driverData_5['heavy_license_type'],
            'license_expiration_date' => $driverData_5['license_expiration_date'],
            'tcc' => $driverData_5['tcc'],
        ]);
    }

    public function test_create_driver_fails_on_user_type_is_not_none(): void
    {
        $user = User::factory()->create([
            'user_type' => Arr::random(['Técnico', 'Gestor', 'Condutor', 'Administrador']),
        ]);

        $driverData = [
            'user_id' => $user->id,
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => 1,
            'heavy_license_type' => 'Mercadorias',
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $driverData);

        $response->assertSessionHasErrors(['user_id']);

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driverData['user_id'],
            'license_number' => $driverData['license_number'],
            'heavy_license' => $driverData['heavy_license'],
            'heavy_license_type' => $driverData['heavy_license_type'],
            'license_expiration_date' => $driverData['license_expiration_date'],
            'tcc' => $driverData['tcc'],
        ]);
    }

    public function test_user_can_edit_a_driver(): void
    {
        $heavyLicense = rand(0,1);
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;
        $childrenTransportationCertified = rand(0,1);
        $certificationExpiration = $childrenTransportationCertified ? fake()->date() : null;

        $driver = Driver::factory()->create([
            'user_id' => User::factory()->create()->id,
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType,
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => $childrenTransportationCertified,
            'tcc_expiration_date' => $certificationExpiration,
        ]);
    
        $newHeavyLicense = rand(0,1);
        $newHeavyLicenseType = $newHeavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;
        $newChildrenTransportationCertified = rand(0,1);
        $newCertificationExpiration = $newChildrenTransportationCertified ? fake()->date() : null;

        $updatedData = [
            'user_id' => $driver->user_id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']),
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => $newHeavyLicense,
            'heavy_license_type' => $newHeavyLicenseType,
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => $newChildrenTransportationCertified,
            'tcc_expiration_date' => $newCertificationExpiration,
        ]; 
        
        $response = $this
            ->actingAs($this->user)
            ->put((route('drivers.edit', $driver->user_id)), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'user_id' => $driver->user_id,
            'license_number' => $updatedData['license_number'],
            'heavy_license' => $newHeavyLicense,
            'heavy_license_type' => $newHeavyLicenseType,
            'license_expiration_date' => $updatedData['license_expiration_date'],
            'tcc' => $updatedData['tcc'],
            'tcc_expiration_date' => $updatedData['tcc_expiration_date'],
        ]);
    }

    public function test_user_can_delete_a_driver(): void
    {
        $driver = Driver::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('drivers.delete', $driver->user_id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseMissing('drivers', [
            'user_id' => $driver->user_id,
        ]);
    }

    public function test_driver_creation_handles_exception()
    {
        $data = [
            'user_id' => User::factory()->create()->id,
            'license_number' => $this->getRandomRegionIdentifier() . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . ' ' . rand(0, 9),
            'heavy_license' => 0,
            'license_expiration_date' => fake()->date('Y-m-d', now()->addYears(rand(1,5))),
            'tcc' => 0,
        ];

        // Mock the Driver model to throw an exception
        $this->mock(Driver::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create driver route
        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('drivers.index')); // Ensure it redirects back to the form
    }
}
