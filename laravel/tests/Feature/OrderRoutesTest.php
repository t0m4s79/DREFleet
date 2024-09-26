<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\OrderRoute;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MatanYadaev\EloquentSpatial\Objects\LineString;

class OrderRoutesTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function generateRandomCoordinatesArray(): array
    {
        $numPoints = rand(3, 10);
        $coordinates = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $coordinates[] = [
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
            ];
        }

        // Ensure the polygon is closed by adding the first point at the end if necessary
        if ($coordinates[0] !== end($coordinates)) {
            $coordinates[] = $coordinates[0];
        }

        return $coordinates;
    }

    private function coordinatesToPolygon($coordinates) {
        $points = [];

            foreach ($coordinates as $coordinate) {
                $point = new Point($coordinate["lat"], $coordinate["lng"]);
                
                $points[] = $point;
            }

            // Ensure the polygon is closed by adding the first point at the end if necessary
            if ($points[0] !== end($points)) {
                $points[] = $points[0];
            }

            $area = new Polygon([
                new LineString($points),
            ]);

            return $area;
    }

    public function test_order_routes_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/orderRoutes');

        $response->assertOk();
    }

    public function test_order_route_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/orderRoutes/create');

        $response->assertOk();
    }

    public function test_order_route_edit_page_is_displayed(): void
    {
        $orderRoute = OrderRoute::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/orderRoutes/edit/{$orderRoute->id}");

        $response->assertOk();
    }

    public function test_user_can_create_an_order_route(): void
    {  
        $coordinates = $this->generateRandomCoordinatesArray();

        $orderRouteData = [
            'name' => fake()->company(),
            'area_coordinates' => $coordinates,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/orderRoutes/create', $orderRouteData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orderRoutes');

        $expectedArea = $this->coordinatesToPolygon($coordinates);

        $orderRoute = OrderRoute::where('name', $orderRouteData['name'])->first();

        $this->assertDatabaseHas('order_routes', [
            'name' => $orderRouteData['name'],
        ]);

        $this->assertEquals($expectedArea,$orderRoute->area);
    }

    public function test_user_can_create_an_order_route_with_drivers_and_technicians(): void
    {  
        $drivers = Driver::factory(rand(1,3))->create();
        $technicians = User::factory(rand(1,3))->create([
            'user_type' => 'Técnico',
        ]);

        $coordinates = $this->generateRandomCoordinatesArray();

        $driverIds = $drivers->pluck('user_id')->toArray();
        $technicianIds = $technicians->pluck('id')->toArray();

        $orderRouteData = [
            'name' => fake()->company(),
            'area_coordinates' => $coordinates,
            'usual_drivers' => $driverIds,
            'usual_technicians' => $technicianIds,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/orderRoutes/create', $orderRouteData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orderRoutes');

        $expectedArea = $this->coordinatesToPolygon($coordinates);

        $orderRoute = OrderRoute::where('name', $orderRouteData['name'])->first();

        $this->assertDatabaseHas('order_routes', [
            'name' => $orderRouteData['name'],
        ]);

        foreach ($driverIds as $driverId) {
            $this->assertDatabaseHas('driver_order_route', [
                'driver_user_id' => $driverId,
                'order_route_id' => $orderRoute->id,
            ]);
        }
        
        foreach ($technicianIds as $technicianId) {
            $this->assertDatabaseHas('order_route_user', [
                'user_id' => $technicianId,
                'order_route_id' => $orderRoute->id,
            ]);
        }

        $this->assertEquals($expectedArea,$orderRoute->area);
    }

    public function test_user_can_edit_an_order_route(): void
    {
        $orderRoute = OrderRoute::factory()->create();

        $drivers = Driver::factory(rand(1,3))->create();
        $technicians = User::factory(rand(1,3))->create([
            'user_type' => 'Técnico',
        ]);

        $coordinates = $this->generateRandomCoordinatesArray();

        $driverIds = $drivers->pluck('user_id')->toArray();
        $technicianIds = $technicians->pluck('id')->toArray();

        $updatedData = [
            'name' => fake()->company(),
            'area_coordinates' => $coordinates,
            'usual_drivers' => $driverIds,
            'usual_technicians' => $technicianIds,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/orderRoutes/edit/{$orderRoute->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orderRoutes');

        $expectedArea = $this->coordinatesToPolygon($coordinates);

        $orderRoute->refresh();

        $this->assertDatabaseHas('order_routes', [
            'name' => $updatedData['name'],
        ]);

        foreach ($driverIds as $driverId) {
            $this->assertDatabaseHas('driver_order_route', [
                'driver_user_id' => $driverId,
                'order_route_id' => $orderRoute->id,
            ]);
        }
        
        foreach ($technicianIds as $technicianId) {
            $this->assertDatabaseHas('order_route_user', [
                'user_id' => $technicianId,
                'order_route_id' => $orderRoute->id,
            ]);
        }

        $this->assertEquals($expectedArea, $orderRoute->area);
    }

    public function test_user_can_delete_an_order_route(): void
    {
        $orderRoute = OrderRoute::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete("/orderRoutes/delete/{$orderRoute->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orderRoutes');

        $this->assertDatabaseMissing('order_routes', [
            'id' => $orderRoute->id,
        ]);
    }

    public function test_order_route_creation_handles_exception()
    {
        $coordinates = $this->generateRandomCoordinatesArray();

        $incomingFields = [
            'name' => fake()->company(),
            'area_coordinates' => $coordinates,
        ];       

        // Mock the Order model to throw an exception
        $this->mock(OrderRoute::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create order route
        $response = $this
            ->actingAs($this->user)
            ->post('/orderRoutes/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/orderRoutes');
    }
}
