<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Models\VehicleMaintenanceReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleMaintenanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['user_type' => 'Administrador']);
    }

    private function generateItemsCost()
    {
        $items = [
            'pneus (' . rand(1,4) .')' => (string) rand(50, 150),
            'óleo' => (string) rand(10, 30),
            'filtro de ar' => (string) rand(15, 25),
            'troca de pastilhas de travão' => (string) rand(20, 60),
            'alinhamento de direção' => (string) rand(30, 70),
            'revisão geral' => (string) rand(100, 200),
            'troca de bateria' => (string) rand(80, 120),
            'fluido de travão' => (string) rand(10, 20),
            'troca de amortecedores' => (string) rand(50, 100),
            'balanceamento de rodas' => (string) rand(20, 40),
            'troca de filtro de óleo' => (string) rand(10, 25),
            'inspeção de suspensão' => (string) rand(30, 80),
        ];
    
        // Retrieve a random subset of items as associative array
        $selectedItemsKeys = array_rand($items, rand(2, count($items)));
        
        // Ensure $selectedItemsKeys is an array in case only one item is selected
        if (!is_array($selectedItemsKeys)) {
            $selectedItemsKeys = [$selectedItemsKeys];
        }
        
        // Get the selected items with original keys
        $selectedItems = [];
        foreach ($selectedItemsKeys as $key) {
            $selectedItems[$key] = $items[$key];
        }
    
        return $selectedItems;
    }

    public function test_maintenance_report_belongs_to_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $report = VehicleMaintenanceReport::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $this->assertTrue($report->vehicle->is($vehicle));
    }

    public function test_vehicle_maintenance_report_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleMaintenanceReports.showCreate'));

        $response->assertOk();
    }

    public function test_vehicle_maintenance_report_edit_page_is_displayed(): void
    {
        $report = VehicleMaintenanceReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('vehicleMaintenanceReports.showEdit', $report->id));

        $response->assertOk();
    }

    public function test_user_can_create_a_vehicle_maintenance_report(): void
    {       
        $beginDate = fake()->date();
        $endDate = rand(0,1) ? Carbon::parse($beginDate)->addDays(rand(1,10)) : null;

        if ($beginDate > now()) {
            $status = 'Agendado';

        } else if (isset($endDate) && $endDate > now()) {
            $status = 'Finalizado';

        } else {
            $status = 'A decorrer';
        }

        $reportData = [
            'begin_date' => $beginDate,
            'end_date' => $endDate,
            'type' => Arr::random(['Manutenção', 'Anomalia', 'Reparação', 'Outros']),
            'description' => fake()->sentence(),
            'kilometrage' => rand(1,100000),
            'total_cost' => fake()->randomFloat(2, 10, 200),
            'items_cost' => $this->generateItemsCost(),
            'service_provider' => fake()->title(),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleMaintenanceReports.create'), $reportData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.maintenanceReports', $reportData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_maintenance_reports', [
            'begin_date' => $reportData['begin_date'],
            'end_date' => $reportData['end_date'],
            'type' => $reportData['type'],
            'description' => $reportData['description'],
            'kilometrage' => $reportData['kilometrage'],
            'total_cost' => $reportData['total_cost'],
            'items_cost' => json_encode($reportData['items_cost']),
            'service_provider' => $reportData['service_provider'],
            'status' => $status,
            'vehicle_id' => $reportData['vehicle_id'],
        ]);        
    }

    public function test_user_can_edit_a_vehicle_maintenance_report(): void
    {
        $report = VehicleMaintenanceReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $beginDate = fake()->date();
        $endDate = rand(0,1) ? Carbon::parse($beginDate)->addDays(rand(1,10)) : null;

        if ($beginDate > now()) {
            $status = 'Agendado';

        } else if (isset($endDate) && $endDate > now()) {
            $status = 'Finalizado';

        } else {
            $status = 'A decorrer';
        }

        $updatedData = [
            'begin_date' => $beginDate,
            'end_date' => $endDate,
            'type' => Arr::random(['Manutenção', 'Anomalia', 'Reparação', 'Outros']),
            'description' => fake()->sentence(),
            'kilometrage' => rand(1,100000),
            'total_cost' => fake()->randomFloat(2, 10, 200),
            'items_cost' => $this->generateItemsCost(),
            'service_provider' => fake()->title(),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('vehicleMaintenanceReports.edit', $report->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.maintenanceReports', $updatedData['vehicle_id']));

        $this->assertDatabaseHas('vehicle_maintenance_reports', [
            'begin_date' => $updatedData['begin_date'],
            'end_date' => $updatedData['end_date'],
            'type' => $updatedData['type'],
            'description' => $updatedData['description'],
            'kilometrage' => $updatedData['kilometrage'],
            'total_cost' => $updatedData['total_cost'],
            'items_cost' => json_encode($updatedData['items_cost']),
            'service_provider' => $updatedData['service_provider'],
            'status' => $status,
            'vehicle_id' => $updatedData['vehicle_id'],
        ]);
    }

    public function test_user_can_delete_a_vehicle_maintenance_report(): void
    {
        $report = VehicleMaintenanceReport::factory()->create([
            'vehicle_id' => Vehicle::factory(),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('vehicleMaintenanceReports.delete', $report->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.maintenanceReports', $report->vehicle->id));

        $this->assertDatabaseMissing('vehicle_maintenance_reports', [
            'id' => $report->id,
        ]);
    }

    public function test_vehicle_creation_handles_exception()
    {
        $date = fake()->date();

        $data = [
            'begin_date' => $date,
            'end_date' => Carbon::parse($date)->addDays(rand(1,10)),
            'type' => Arr::random(['Manutenção', 'Anomalia', 'Reparação', 'Outros']),
            'description' => fake()->sentence(),
            'kilometrage' => rand(1,100000),
            'total_cost' => fake()->randomFloat(2, 10, 200),
            'items_cost' => $this->generateItemsCost(),
            'service_provider' => fake()->title(),
            'vehicle_id' => Vehicle::factory()->create()->id,
        ];

        // Mock the Vehicle Maintenance Report model to throw an exception
        $this->mock(VehicleMaintenanceReport::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create vehicle maintenance report route
        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicleMaintenanceReports.create'), $data);

        // Assert: Check if the catch block was executed
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.maintenanceReports',  $data['vehicle_id']));
    }
}
