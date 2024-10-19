<?php

namespace Tests\Feature;

use App\Models\Driver;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleKilometrageReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleKilometrageReportTest extends TestCase
{    
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_kilometrage_report_belongs_to_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $report = VehicleKilometrageReport::factory()->create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory(),
        ]);

        $this->assertTrue($report->vehicle->is($vehicle));
    }

    public function test_kilometrage_report_belongs_to_a_driver(): void
    {
        $driver = Driver::factory()->create();

        $report = VehicleKilometrageReport::factory()->create([
            'driver_id' => $driver->user_id,
            'vehicle_id' => Vehicle::factory(),
        ]);

        $this->assertTrue($report->driver->is($driver));
    }

    public function test_vehicle_kilometrage_report_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleKilometrageReports.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_kilometrage_report_edit_page_is_displayed(): void
    {
        $report = VehicleKilometrageReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleKilometrageReports.showEdit', $report->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_kilometrage_report(): void
    {        
        $reportData = [
            'date' => fake()->date(),
            'begin_kilometrage' => rand(1,100),
            'end_kilometrage' => rand(100,200),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->user_id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleKilometrageReports.create'), $reportData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.kilometrageReports', $reportData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_kilometrage_reports', $reportData);
    }

    public function test_user_can_edit_a_vehicle_kilometrage_report(): void
    {
        $report = VehicleKilometrageReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
        ]);

        $updatedData = [
            'date' => fake()->date(),
            'begin_kilometrage' => rand(1,100),
            'end_kilometrage' => rand(100,200),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->user_id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleKilometrageReports.edit', $report->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.kilometrageReports', $updatedData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_kilometrage_reports', $updatedData);
    }

    public function test_user_can_delete_a_vehicle(): void
    {
        $vehicleDocument = VehicleKilometrageReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicleKilometrageReports.delete', $vehicleDocument->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.kilometrageReports', $vehicleDocument->vehicle->id));

        $this->assertDatabaseMissing('vehicle_kilometrage_reports', [
            'id' => $vehicleDocument->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        $data = [
            'date' => fake()->date(),
            'begin_kilometrage' => rand(1,100),
            'end_kilometrage' => rand(100,200),
            'vehicle_id' => Vehicle::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->user_id,
        ];

        // Mock the Vehicle Document model to throw an exception
        $this->mock(VehicleKilometrageReport::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle document route
        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleKilometrageReports.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.kilometrageReports',  $data['vehicle_id']));
    }
}
