<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use App\Models\VehicleDocument;
use App\Models\VehicleAccessory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\VehicleKilometrageReport;
use App\Models\VehicleMaintenanceReport;
use App\Models\VehicleRefuelRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class VehicleTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }

    public function test_vehicle_getters(): void
    {
        // Create a sample notification with specific created_at and updated_at values
        $vehicle = Vehicle::factory()->create([
            'created_at' => Carbon::create(2023, 10, 1, 12, 0, 0),  // 01-10-2023 12:00:00
            'updated_at' => Carbon::create(2023, 10, 2, 15, 30, 45), // 02-10-2023 15:30:45
        ]);

        // Get the formatted created_at and updated_at attributes
        $formattedCreatedAt = $vehicle->created_at;
        $formattedUpdatedAt = $vehicle->updated_at;

        // Assert that the created_at is in the correct format
        $this->assertEquals('01-10-2023 12:00:00', $formattedCreatedAt);

        // Assert that the updated_at is in the correct format
        $this->assertEquals('02-10-2023 15:30:45', $formattedUpdatedAt);
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

    public function test_vehicle_has_many_documents(): void
    {
        $vehicle = Vehicle::factory()->create();

        $documents = VehicleDocument::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
        ]);
        
        foreach ($documents as $document) {
            $this->assertTrue($vehicle->documents->contains($document));
        }
    }

    public function test_vehicle_has_many_accessories(): void
    {
        $vehicle = Vehicle::factory()->create();

        $accessories = VehicleAccessory::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
        ]);

        foreach ($accessories as $accessory) {
            $this->assertTrue($vehicle->accessories->contains($accessory));
        }
    }

    public function test_vehicle_has_many_kilometrage_reports(): void
    {
        $vehicle = Vehicle::factory()->create();

        $reports = VehicleKilometrageReport::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory(),
        ]);

        foreach ($reports as $report) {
            $this->assertTrue($vehicle->kilometrageReports->contains($report));
        }
    }

    public function test_vehicle_has_many_refuel_requests(): void
    {
        $vehicle = Vehicle::factory()->create();

        $requests = VehicleRefuelRequest::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
        ]);

        foreach ($requests as $request) {
            $this->assertTrue($vehicle->refuelRequests->contains($request));
        }
    }

    public function test_vehicle_has_many_maintenance_reports(): void
    {
        $vehicle = Vehicle::factory()->create();

        $reports = VehicleMaintenanceReport::factory()->count(3)->create([
            'vehicle_id' => $vehicle->id,
        ]);

        foreach ($reports as $report) {
            $this->assertTrue($vehicle->maintenanceReports->contains($report));
        }
    }

    public function test_notifications_related_vehicle()
    {
        // User who receives notification
        $user = User::factory()->create();

        // Who the notification is about
        $vehicle = Vehicle::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'related_entity_type' => Vehicle::class,
            'related_entity_id' => $vehicle->id,
            'type' => 'Veículo',
            'title' => 'Vehicle Notification',
            'message' => 'You have a notification about the vehicle: ' . $vehicle->id,
            'is_read' => false,
        ]);

        $this->assertCount(1, $vehicle->notifications);
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_vehicles_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.index'));

        $response->assertOk();
    }

    public function test_vehicle_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_edit_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.showEdit', $vehicle->id));

        $response->assertOk();
    }

    public function test_vehicle_accessories_and_documents_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.documentsAndAccessories', $vehicle->id));

        $response->assertOk();
    }

    public function test_vehicle_kilometrage_reports_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.kilometrageReports', $vehicle->id));

        $response->assertOk();
    }

    public function test_vehicle_refuel_requests_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.refuelRequests', $vehicle->id));

        $response->assertOk();
    }

    public function test_vehicle_maintenance_reports_page_is_displayed(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicles.maintenanceReports', $vehicle->id));

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
            'tcc' => fake()->boolean(),
            'yearly_allowed_tows' => rand(0,10),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicles.create'), $vehicleData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));


        $this->assertDatabaseHas('vehicles', $vehicleData);
    }

    public function test_user_can_create_a_vehicle_with_a_image(): void
    {
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        Storage::fake('local');
        $fakeImage = UploadedFile::fake()->image('vehicle.jpg');

        $vehicleData = [
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'tcc' => fake()->boolean(),
            'yearly_allowed_tows' => rand(0,10),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
            'image' => $fakeImage,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicles.create'), $vehicleData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));
    
        $vehicle = Vehicle::where('license_plate', $vehicleData['license_plate'])->first();

        $this->assertDatabaseHas('vehicles', [
            'make' => $vehicleData['make'],
            'model' => $vehicleData['model'],
            'license_plate' => $vehicleData['license_plate'],
            'year' => $vehicleData['year'],
            'heavy_vehicle' => $vehicleData['heavy_vehicle'],
            'heavy_type' => $vehicleData['heavy_type'],
            'wheelchair_adapted' => $vehicleData['wheelchair_adapted'],
            'wheelchair_certified' => $vehicleData['wheelchair_certified'],
            'tcc' => $vehicleData['tcc'],
            'yearly_allowed_tows' => $vehicleData['yearly_allowed_tows'],
            'capacity' => $vehicleData['capacity'],
            'fuel_consumption' => $vehicleData['fuel_consumption'],
            'status' => $vehicleData['status'],
            'current_month_fuel_requests' => $vehicleData['current_month_fuel_requests'],
            'fuel_type' => $vehicleData['fuel_type'],
            'current_kilometrage' => $vehicleData['current_kilometrage'],
            'image_path' => $vehicle->image_path,
        ]);

        Storage::disk('local')->assertExists($vehicle->image_path);
    }

    public function test_user_can_edit_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

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
            'tcc' => fake()->boolean(),
            'yearly_allowed_tows' => rand(0,10),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicles.edit', $vehicle->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', $updatedData);
    }

    public function test_user_can_edit_a_vehicle_and_add_an_image(): void
    {
        Storage::fake('local');
        $fakeImage = UploadedFile::fake()->image('vehicle.jpg');

        $vehicle = Vehicle::factory()->create();

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
            'tcc' => fake()->boolean(),
            'yearly_allowed_tows' => rand(0,10),
            'capacity' => rand(5, 15),
            'fuel_consumption' => rand(2, 10),
            'status' => Arr::random(['Disponível', 'Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0, 6),
            'fuel_type' => Arr::random(['Gasóleo', 'Gasolina 95', 'Gasolina 98', 'Híbrido', 'Elétrico']),
            'current_kilometrage' => rand(1, 200000),
            'image_path' => $vehicle->image_path,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicles.edit', $vehicle->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

            $vehicle = Vehicle::where('license_plate', $updatedData['license_plate'])->first();

            $this->assertDatabaseHas('vehicles', [
                'make' => $updatedData['make'],
                'model' => $updatedData['model'],
                'license_plate' => $updatedData['license_plate'],
                'year' => $updatedData['year'],
                'heavy_vehicle' => $updatedData['heavy_vehicle'],
                'heavy_type' => $updatedData['heavy_type'],
                'wheelchair_adapted' => $updatedData['wheelchair_adapted'],
                'wheelchair_certified' => $updatedData['wheelchair_certified'],
                'tcc' => $updatedData['tcc'],
                'yearly_allowed_tows' => $updatedData['yearly_allowed_tows'],
                'capacity' => $updatedData['capacity'],
                'fuel_consumption' => $updatedData['fuel_consumption'],
                'status' => $updatedData['status'],
                'current_month_fuel_requests' => $updatedData['current_month_fuel_requests'],
                'fuel_type' => $updatedData['fuel_type'],
                'current_kilometrage' => $updatedData['current_kilometrage'],
                'image_path' => $vehicle->image_path,
            ]);
    
            Storage::disk('local')->assertExists($vehicle->image_path);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicles.delete', $vehicle->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        // Prepare the incoming fields
        $data = [
            'make' => Arr::random(['Ford', 'Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(10, 99) . chr(rand(65, 90)) . chr(rand(65, 90)) . rand(10, 99),            
            'year' => rand(1960, 2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'tcc' => fake()->boolean(),
            'yearly_allowed_tows' => rand(0,10),
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
            ->post(route('vehicles.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('vehicles.index'));
    }
}
