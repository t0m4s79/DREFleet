<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Database\Factories\ManagerFactory;
use Database\Factories\TechnicianFactory;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;

//TODO: THROW EXCEPTION ERRORS
class OrderTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function generateRandomTrajectory($startLat, $startLng, $endLat, $endLng)
    {
        $points = [];
        $numPoints = rand(2,6); // Number of points in the trajectory
        
        for ($i = 0; $i <= $numPoints; $i++) {
            $lat = $startLat + ($endLat - $startLat) * ($i / $numPoints);
            $lng = $startLng + ($endLng - $startLng) * ($i / $numPoints);
            $points[] = [
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        return $points;
    }

    private function generateRandomPlacesAndKids() {
        $places = Place::factory()->count(rand(1,5))->create();

        // Prepare the data for the order creation
        $placesData = [];
        foreach ($places as $place) {
            if (rand(0, 1) === 1) { // 50% chance to have a kid
                $kid = Kid::factory()->create();
                $kid->places()->attach($place->id);
                $placesData[] = [
                    'place_id' => $place->id,
                    'kid_id' => $kid->id,
                ];
            } else {
                $placesData[] = [
                    'place_id' => $place->id,
                ];
            }
        }

        return $placesData;
    }

    public function test_orders_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/orders');

        $response->assertOk();
    }

    public function test_order_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/orders/create');

        $response->assertOk();
    }

    public function test_order_edit_page_is_displayed(): void
    {
        $order = Order::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/orders/edit/{$order->id}");

        $response->assertOk();
    }

    public function test_user_can_create_an_order(): void
    {  
        $placesData = $this->generateRandomPlacesAndKids();

        $beginLatitude = fake()->latitude();
        $beginLongitude = fake()->longitude();

        $endLatitude = fake()->latitude();
        $endLongitude = fake()->longitude();

        $trajectory = $this->generateRandomTrajectory($beginLatitude, $beginLongitude, $endLatitude, $endLongitude);
     
        $orderData = [
            'trajectory' => json_encode($trajectory),
            'begin_address' => fake()->address(),
            'begin_latitude' => $beginLatitude,
            'begin_longitude' => $beginLongitude,
            'end_address' => fake()->address(),
            'end_latitude' => $endLatitude,
            'end_longitude' => $endLongitude,
            'planned_begin_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'planned_end_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0'])->id,
            'driver_id' => Driver::factory()->create()->user_id,
            'technician_id' => TechnicianFactory::new()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post('/orders/create', $orderData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orders');


        $this->assertDatabaseHas('orders', [
            'trajectory' => $orderData['trajectory'],
            'begin_address' => $orderData['begin_address'],
            'end_address' => $orderData['end_address'],
            'planned_begin_date' => $orderData['planned_begin_date'],
            'planned_end_date' => $orderData['planned_end_date'],
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);

        $order = Order::where('begin_address', $orderData['begin_address'])
                    ->where('end_address', $orderData['end_address'])
                    ->where('planned_begin_date', $orderData['planned_begin_date'])
                    ->where('planned_end_date', $orderData['planned_end_date'])
                    ->where('vehicle_id', $orderData['vehicle_id'])
                    ->where('driver_id', $orderData['driver_id'])
                    ->where('technician_id', $orderData['technician_id'])
                    ->first();
        
        $expectedBeginCoordinates = new Point($orderData['begin_latitude'], $orderData['begin_longitude']);
        $expectedEndCoordinates = new Point($orderData['end_latitude'], $orderData['end_longitude']);

        $this->assertEquals($expectedBeginCoordinates->latitude, $order->begin_coordinates->latitude);
        $this->assertEquals($expectedEndCoordinates->latitude, $order->end_coordinates->latitude);

        $this->assertEquals($expectedBeginCoordinates->longitude, $order->begin_coordinates->longitude);
        $this->assertEquals($expectedEndCoordinates->longitude, $order->end_coordinates->longitude);
    }


    public function test_user_can_edit_an_order(): void
    {
        $order = Order::factory()->create();

        $beginLatitude = fake()->latitude();
        $beginLongitude = fake()->longitude();

        $endLatitude = fake()->latitude();
        $endLongitude = fake()->longitude();

        $trajectory = $this->generateRandomTrajectory($beginLatitude, $beginLongitude, $endLatitude, $endLongitude);
    
        $updatedData = [
            'trajectory' => json_encode($trajectory),
            'begin_address' => fake()->address(),
            'begin_latitude' => $beginLatitude,
            'begin_longitude' => $beginLongitude,
            'end_address' => fake()->address(),
            'end_latitude' => $endLatitude,
            'end_longitude' => $endLongitude,
            'planned_begin_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'planned_end_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0'])->id,
            'driver_id' => Driver::factory()->create()->user_id,
            'technician_id' => TechnicianFactory::new()->create()->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put("/orders/edit/{$order->id}", $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orders');

        $order->refresh();

        $expectedBeginCoordinates = new Point($updatedData['begin_latitude'], $updatedData['begin_longitude']);
        $expectedEndCoordinates = new Point($updatedData['end_latitude'], $updatedData['end_longitude']);

        $this->assertDatabaseHas('orders', [
            'trajectory' => $order->trajectory,
            'begin_address' => $order->begin_address, 
            'end_address' => $order->end_address,
            'planned_end_date' => $order->planned_begin_date,
            'planned_end_date' => $order->planned_end_date,
            'order_type' => $order->order_type,
            'vehicle_id' => $order->vehicle_id,
            'driver_id' => $order->driver_id,
            'technician_id' => $order->technician_id,
        ]);
        
        $this->assertEquals($expectedBeginCoordinates->latitude, $order->begin_coordinates->latitude);
        $this->assertEquals($expectedEndCoordinates->latitude, $order->end_coordinates->latitude);

        $this->assertEquals($expectedBeginCoordinates->longitude, $order->begin_coordinates->longitude);
        $this->assertEquals($expectedEndCoordinates->longitude, $order->end_coordinates->longitude);
    }

    public function test_user_can_delete_an_order(): void
    {
        $order = Order::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->delete("/orders/delete/{$order->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/orders');

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }

    public function test_user_can_approve_a_order(): void
    {
        //TODO: BACK-END AND FRONT-END AND ONLY THEN TESTS
    }

    public function test_approve_order_fails_with_invalid_manager_id(): void
    {
        //TODO: BACK-END AND FRONT-END AND ONLY THEN TESTS
    }

    public function test_user_can_set_order_actual_begin_date(): void
    {
        //TODO: BACK-END AND FRONT-END AND ONLY THEN TESTS
    }

    public function test_user_can_set_order_actual_end_date(): void
    {
        //TODO: BACK-END AND FRONT-END AND ONLY THEN TESTS
    }

    public function test_order_creation_handles_exception()
    {
        $placesData = $this->generateRandomPlacesAndKids();

        $beginLatitude = fake()->latitude();
        $beginLongitude = fake()->longitude();

        $endLatitude = fake()->latitude();
        $endLongitude = fake()->longitude();

        $trajectory = $this->generateRandomTrajectory($beginLatitude, $beginLongitude, $endLatitude, $endLongitude);

        $incomingFields = [
            'trajectory' => json_encode($trajectory),
            'begin_address' => fake()->address(),
            'begin_latitude' => $beginLatitude,
            'begin_longitude' => $beginLongitude,
            'end_address' => fake()->address(),
            'end_latitude' => $endLatitude,
            'end_longitude' => $endLongitude,
            'planned_begin_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'planned_end_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0'])->id,
            'driver_id' => Driver::factory()->create()->user_id,
            'technician_id' => TechnicianFactory::new()->create()->id,
        ];        

        // Mock the Order model to throw an exception
        $this->mock(Order::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create order route
        $response = $this
            ->actingAs($this->user)
            ->post('/orders/create', $incomingFields);

        // Assert: Check if the catch block was executed
        $response->assertRedirect('/orders');
    }
}
