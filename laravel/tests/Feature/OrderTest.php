<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\OrderStop;
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
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);

        
        $order = Order::where('trajectory', json_encode($trajectory))
                    ->where('vehicle_id', $orderData['vehicle_id'])
                    ->where('driver_id', $orderData['driver_id'])
                    ->where('technician_id', $orderData['technician_id'])
                    ->first();
        
        // Count the number of order stops associated with the created order
        $orderStopsCount = OrderStop::where('order_id', $order->id)->count();
        
        // Assert that the number of order stops matches the size of the placesData array
        $this->assertEquals(count($placesData), $orderStopsCount);
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

        $this->assertDatabaseHas('orders', [
            'trajectory' => $order->trajectory,
            'order_type' => $order->order_type,
            'vehicle_id' => $order->vehicle_id,
            'driver_id' => $order->driver_id,
            'technician_id' => $order->technician_id,
        ]);
    }

    public function test_user_can_add_and_removes_places_to_an_order(): void
    {
        $order = Order::factory()->create();
        $removeOrderStop = OrderStop::where('order_id', $order->id)->inRandomOrder()->first();
        $addPlace = Place::factory()->create();

        $this->assertDatabaseHas('order_stops', [
            'id' => $removeOrderStop->id,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseMissing('order_stops', [
            'id' => $addPlace->id,
            'order_id' => $order->id,
        ]);

        $beginLatitude = fake()->latitude();
        $beginLongitude = fake()->longitude();

        $endLatitude = fake()->latitude();
        $endLongitude = fake()->longitude();

        $trajectory = $this->generateRandomTrajectory($beginLatitude, $beginLongitude, $endLatitude, $endLongitude);
    
        $updatedData = [
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'addPlaces' => [
                [
                'place_id' => $addPlace->id,  // Add necessary fields
                ]
            ],

            'removePlaces' => [$removeOrderStop->id],

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

        $this->assertDatabaseMissing('order_stops', [
            'id' => $removeOrderStop->id,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('order_stops', [
            'place_id' => $addPlace->id,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'trajectory' => $order->trajectory,
            'order_type' => $order->order_type,
            'vehicle_id' => $order->vehicle_id,
            'driver_id' => $order->driver_id,
            'technician_id' => $order->technician_id,
        ]);
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

    public function test_user_can_approve_a_order(): void       //TODO: NEEDS FIXING
    {
        $manager = User::factory()->create([
            'user_type' => 'Gestor',
        ]);

        $order = Order::factory()->create([
            'approved_date' => null,
            'manager_id' => null,
        ]);

        $this->actingAs($manager);

        $response = $this->put(route('orders.approve', $order), [
            'manager_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'manager_id' => $manager->id,
        ]);

        $response->assertRedirect(route('orders.index'));      
    }

    public function test_approve_order_fails_with_invalid_manager_id(): void    //TODO: NEEDS IMPLEMENTING
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
