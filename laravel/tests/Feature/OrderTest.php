<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Database\Factories\TechnicianFactory;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function generateTrajectory($startLat, $startLng, $endLat, $endLng)
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

    //TODO: CHECK WHY THIS TEST FAILS AND IMPLEMENT REST OF TESTS
    public function test_user_can_create_an_order(): void
    {  
        $beginLatitude = fake()->latitude();
        $beginLongitude = fake()->longitude();

        $endLatitude = fake()->latitude();
        $endLongitude = fake()->longitude();

        $trajectory = $this->generateTrajectory($beginLatitude, $beginLongitude, $endLatitude, $endLongitude);
     
        $orderData = [
            'trajectory' => json_encode($trajectory),
            'begin_address' => fake()->address(),
            'begin_latitude' => $beginLatitude,
            'begin_longitude' => $beginLongitude,
            'end_address' => fake()->address(),
            'end_latitude' => $endLatitude,
            'end_longitude' => $endLongitude,
            'begin_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'end_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            
            'vehicle_id' => Vehicle::factory()->create()->id,
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
            'begin_address' => $orderData['begin_address'],
            'end_address' => $orderData['end_address'],
            'begin_date' => $orderData['begin_date'],
            'end_date' => $orderData['end_date'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);

        $order = Order::where('begin_address', $orderData['begin_address'])
                    ->where('end_address', $orderData['end_address'])
                    ->where('begin_date', $orderData['begin_date'])
                    ->where('end_date', $orderData['end_date'])
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

        //TODO: ASSERT TRAJECTORY
    }


    public function test_user_can_edit_an_order(): void
    {
        
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
        
    }

    public function test_order_creation_handles_exception()
    {
        // $incomingFields = [
            
        // ];

        // // Mock the Order model to throw an exception
        // $this->mock(Order::class, function ($mock) {
        //     $mock->shouldReceive('create')
        //         ->andThrow(new \Exception('Database error'));
        // });

        // // Act: Send a POST request to the create order route
        // $response = $this
        //     ->actingAs($this->user)
        //     ->post('/orders/create', $incomingFields);

        // // Assert: Check if the catch block was executed
        // $response->assertRedirect('/orders');
    }
}
