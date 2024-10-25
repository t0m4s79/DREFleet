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
use App\Models\OrderRoute;
use Illuminate\Support\Arr;
use App\Models\Notification;
use App\Models\OrderOccurrence;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Database\Factories\ManagerFactory;
use Database\Factories\TechnicianFactory;
use Illuminate\Foundation\Testing\WithFaker;
use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Notifications\OrderCreationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        
        $this->driver = Driver::factory()->create();
        User::find($this->driver->user_id)->update(['status' => 'Disponível']);
    }

    private function generateRandomTrajectory()
    {
        $startLat = fake()->latitude();
        $startLng = fake()->longitude();

        $endLat = fake()->latitude();
        $endLng = fake()->longitude();

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

    private function generateRandomPlacesAndKids($withKids) {
        $places = Place::factory()->count(rand(1,5))->create();
        $stopNumber = 1;

        // Prepare the data for the order creation
        $placesData = [];
        foreach ($places as $place) {            
            if (rand(0, 1) === 1 && $withKids) { // 50% chance to have a kid
                $kid = Kid::factory()->create();
                $kid->places()->attach($place->id);
                $placesData[] = [
                    'place_id' => $place->id,
                    'stop_number' => $stopNumber,
                    'time' => rand(1,10),
                    'distance' => rand(1,10),
                    'kid_id' => $kid->id,
                ];

            } else {
                $placesData[] = [
                    'place_id' => $place->id,
                    'stop_number' => $stopNumber,
                    'time' => rand(1,10),
                    'distance' => rand(1,10),
                ];
            }

            $stopNumber++;
        }

        return $placesData;
    }

    public function test_order_has_many_order_stops(): void
    {
        $order = Order::factory()->create();

        $orderStops = OrderStop::factory()->count(10)->create([
            'order_id' => $order->id,
            'place_id' => Place::factory(),
        ]);

        foreach ($orderStops as $orderStop) {
            $this->assertTrue($order->orderStops->contains($orderStop));
        }
    }

    public function test_order_has_many_occurrences(): void
    {
        $order = Order::factory()->create();

        $occurrences = OrderOccurrence::factory()->count(3)->create([
            'order_id' => $order->id,
        ]);

        foreach ($occurrences as $occurrence) {
            $this->assertTrue($order->occurrences->contains($occurrence));
        }
    }

    public function test_order_belongs_to_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $order = Order::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $this->assertTrue($order->vehicle->is($vehicle));
    }

    public function test_order_belongs_to_manager(): void
    {
        $manager = User::factory()->create();

        $order = Order::factory()->create([
            'manager_id' => $manager->id,
            'approved_date' => now(),
        ]);

        $this->assertTrue($order->manager->is($manager));
    }

    public function test_order_belongs_to_technician(): void
    {
        $technician = User::factory()->create();

        $order = Order::factory()->create([
            'technician_id' => $technician->id,
        ]);

        $this->assertTrue($order->technician->is($technician));
    }

    public function test_order_belongs_to_driver(): void
    {
        $driver = Driver::factory()->create();

        $order = Order::factory()->create([
            'driver_id' => $driver->user_id,
        ]);

        $this->assertTrue($order->driver->is($driver));
    }

    public function test_notifications_related_to_order()
    {
        // User who receives notification
        $user = User::factory()->create();

        // Who the notification is about
        $order = Order::factory()->create();

        $notification = Notification::create([
            'user_id' => $user->id,
            'related_entity_type' => Order::class,
            'related_entity_id' => $order->id,
            'type' => 'Pedido',
            'title' => 'Order Notification',
            'message' => 'You have a notification about the order: ' . $order->id,
            'is_read' => false,
        ]);

        $this->assertCount(1, $order->notifications);
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_orders_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('orders.index'));

        $response->assertOk();
    }

    public function test_order_creation_page_is_displayed(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get(route('orders.showCreate'));

        $response->assertOk();
    }

    public function test_order_edit_page_is_displayed(): void
    {
        $order = Order::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('orders.showEdit', $order->id));

        $response->assertOk();
    }

    public function test_order_occurrences_page_is_displayed(): void
    {
        $order = Order::factory()->create();

        $response = $this
            ->actingAs($this->user)
            ->get(route('orders.occurrences', $order->id));

        $response->assertOk();
    }

    public function test_user_can_create_an_order(): void
    {  
        NotificationFacade::fake();

        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);

        $trajectory = $this->generateRandomTrajectory();
     
        $orderData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'order_route_id' => OrderRoute::factory()->create()->id,

            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1','capacity' => 100, 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.index'));


        $this->assertDatabaseHas('orders', [
            'expected_begin_date' => $orderData['expected_begin_date'],
            'expected_end_date' => $orderData['expected_end_date'],
            'expected_time' => $orderData['expected_time'],
            'distance' => $orderData['distance'],
            'trajectory' => $orderData['trajectory'],
            'order_type' => $orderData['order_type'],
            'order_route_id' => $orderData['order_route_id'],
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

        // Assert that the notification was sent
        NotificationFacade::assertSentTo(
            User::findOrFail($orderData['technician_id']),
            OrderCreationNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->getOrder()->id === $order->id;
            }
        );
        
        NotificationFacade::assertSentTo(
            User::findOrFail($orderData['driver_id']),
            OrderCreationNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->getOrder()->id === $order->id;
            }
        );
    }

    // Add kid that uses a wheelchair, vehicle will not be wheelchair adapted
    public function test_order_creation_fails_on_wrong_vehicle_for_kid(): void
    {  
        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);
        
        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create(['wheelchair' => 1]);
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
        ];

        $trajectory = $this->generateRandomTrajectory();
     
        $orderData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'places' => $placesData,
            
            //Not adapted for wheelchairs
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '0', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData);

        $response->assertSessionHasErrors(['places.*.kid_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData['expected_begin_date'],
            'expected_end_date' => $orderData['expected_end_date'],
            'expected_time' => $orderData['expected_time'],
            'distance' => $orderData['distance'],
            'trajectory' => $orderData['trajectory'],
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);
    }

    // Wrong order type for kid transportation
    public function test_order_creation_fails_on_wrong_order_type_with_kids(): void
    {  
        $placesData = $this->generateRandomPlacesAndKids($withKids=true);
        
        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create();
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
        ];

        $trajectory = $this->generateRandomTrajectory();
     
        $orderData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']),
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData);

        $response->assertSessionHasErrors(['places.*.kid_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData['expected_begin_date'],
            'expected_end_date' => $orderData['expected_end_date'],
            'expected_time' => $orderData['expected_time'],
            'distance' => $orderData['distance'],
            'trajectory' => $orderData['trajectory'],
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);
    }

    // Capacity will be 1 with multiple passangers
    public function test_order_creation_fails_on_vehicle_capacity_exceeded(): void
    {  
        $placesData = $this->generateRandomPlacesAndKids($withKids=true);
     
        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create();
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
        ];

        $trajectory = $this->generateRandomTrajectory();

        $orderData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => 'Transporte de Crianças',
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'capacity' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData);

        $response->assertSessionHasErrors(['vehicle_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData['expected_begin_date'],
            'expected_end_date' => $orderData['expected_end_date'],
            'expected_time' => $orderData['expected_time'],
            'distance' => $orderData['distance'],
            'trajectory' => $orderData['trajectory'],
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);
    }

    // Driver will not have heavy license while vehicle is heavy
    public function test_order_creation_fails_on_wrong_driver_for_vehicle(): void
    {  
        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);

        $trajectory = $this->generateRandomTrajectory();
     
        //1 -> heavy vehicle with no heavy license
        $driver_1 = Driver::factory()->create(['heavy_license' => '0']);
        User::find($driver_1->user_id)->update(['status' => 'Disponível']);

        $orderData_1 = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'wheelchair_adapted' => '1'])->id,
            'driver_id' => $driver_1->user_id,
            'technician_id' => TechnicianFactory::new()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData_1);


        $response->assertSessionHasErrors(['driver_id']);
        
        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData_1['expected_begin_date'],
            'expected_end_date' => $orderData_1['expected_end_date'],
            'expected_time' => $orderData_1['expected_time'],
            'distance' => $orderData_1['distance'],
            'trajectory' => $orderData_1['trajectory'],
            'order_type' => $orderData_1['order_type'],
            'vehicle_id' => $orderData_1['vehicle_id'],
            'driver_id' => $orderData_1['driver_id'],
            'technician_id' => $orderData_1['technician_id'],
        ]);

        //2 -> heavy Passangers vehicle with heavy Goods license
        $driver_2 = Driver::factory()->create(['heavy_license' => '1', 'heavy_license_type' => 'Mercadorias']);
        User::find($driver_2->user_id)->update(['status' => 'Disponível']);

        $orderData_2 = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'heavy_type' => 'Passageiros', 'wheelchair_adapted' => '1'])->id,
            'driver_id' => $driver_2->user_id,
            'technician_id' => TechnicianFactory::new()->create()->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData_2);

        $response->assertSessionHasErrors(['driver_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData_2['expected_begin_date'],
            'expected_end_date' => $orderData_2['expected_end_date'],
            'expected_time' => $orderData_2['expected_time'],
            'distance' => $orderData_2['distance'],
            'trajectory' => $orderData_2['trajectory'],
            'order_type' => $orderData_2['order_type'],
            'vehicle_id' => $orderData_2['vehicle_id'],
            'driver_id' => $orderData_2['driver_id'],
            'technician_id' => $orderData_2['technician_id'],
        ]);
    }

    // User is not a technician
    public function test_order_creation_fails_on_wrong_technician(): void
    {  
        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);

        $trajectory = $this->generateRandomTrajectory();
     
        $orderData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => User::factory()->create(['user_type' => Arr::random(['Gestor', 'Condutor', 'Administrador'])])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $orderData);

        $response->assertSessionHasErrors(['technician_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $orderData['expected_begin_date'],
            'expected_end_date' => $orderData['expected_end_date'],
            'expected_time' => $orderData['expected_time'],
            'distance' => $orderData['distance'],
            'trajectory' => $orderData['trajectory'],
            'order_type' => $orderData['order_type'],
            'vehicle_id' => $orderData['vehicle_id'],
            'driver_id' => $orderData['driver_id'],
            'technician_id' => $orderData['technician_id'],
        ]);
    }

    public function test_user_can_edit_an_order(): void
    {
        $order = Order::factory()->create();

        $trajectory = $this->generateRandomTrajectory();
    
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),
            'order_route_id' => OrderRoute::factory()->create()->id,

            'places_changed' => false,
            'places' => [],
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.index'));

        $order->refresh();

        $this->assertDatabaseHas('orders', [
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
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

        $trajectory = $this->generateRandomTrajectory();
    
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'places_changed' => true,
            'places' => [
                [
                'place_id' => $addPlace->id,  // Add necessary fields
                'stop_number' => '1',
                'time' => rand(1,10),
                'distance' => rand(1,10),
                ]
            ],

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.index'));

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
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
        ]);
    }

    // Add kid that uses a wheelchair, vehicle will not be wheelchair adapted
    public function test_order_edit_fails_on_wrong_vehicle_for_kid(): void
    {  
        $order = Order::factory()->create();

        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);

        //Add kid that uses a wheelchair, vehicle will not be wheelchair adapted
        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create(['wheelchair' => 1]);
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
            'stop_number' => count($placesData) + 1,
        ];

        $trajectory = $this->generateRandomTrajectory();
     
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'places_changed' => true,
            'places' => $placesData,
            
            //Not adapted for wheelchairs
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '0', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response->assertSessionHasErrors(['places.*.kid_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
        ]);
    }

    // Wrong order type for kid transportation
    public function test_order_edit_fails_on_wrong_order_type_with_kid(): void
    {  
        $order = Order::factory()->create();

        $placesData = $this->generateRandomPlacesAndKids($withKids = true);

        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create();
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
            'stop_number' => count($placesData) + 1,
        ];

        $trajectory = $this->generateRandomTrajectory();
     
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']),
            
            'places_changed' => true,
            'places' => $placesData,

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response->assertSessionHasErrors(['places.*.kid_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
        ]);
    }

    // Capacity will be 1 with multiple passangers
    public function test_order_edit_fails_on_vehicle_capacity_exceeded(): void
    {  
        $order = Order::factory()->create();

        $placesData = $this->generateRandomPlacesAndKids($withKids = true);

        $newPlace = Place::factory()->create();
        $newKid = Kid::factory()->create();
        $newKid->places()->attach($newPlace->id);

        $placesData[] = [
            'place_id' => $newPlace->id,
            'kid_id' => $newKid->id,
            'stop_number' => count($placesData) + 1,
        ];

        $trajectory = $this->generateRandomTrajectory();
     
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => 'Transporte de Crianças',

            'places_changed' => true,
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'wheelchair_adapted' => '1', 'capacity' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];

        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response->assertSessionHasErrors(['vehicle_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
        ]);
    }

    // Driver will not have heavy license while vehicle is heavy
    public function test_order_edit_fails_on_wrong_driver_for_vehicle(): void
    {
        $order = Order::factory()->create();

        $trajectory = $this->generateRandomTrajectory();

        $driver_1 = Driver::factory()->create(['heavy_license' => '0']);
        User::find($driver_1->user_id)->update(['status' => 'Disponível']);
    
        //1 -> heavy vehicle with no heavy license
        $updatedData_1 = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'places_changed' => false,
            'places' => [],

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $driver_1->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData_1);

        $response->assertSessionHasErrors(['driver_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $updatedData_1['expected_begin_date'],
            'expected_end_date' => $updatedData_1['expected_end_date'],
            'expected_time' => $updatedData_1['expected_time'],
            'distance' => $updatedData_1['distance'],
            'trajectory' => $updatedData_1['trajectory'],
            'order_type' => $updatedData_1['order_type'],
            'vehicle_id' => $updatedData_1['vehicle_id'],
            'driver_id' => $updatedData_1['driver_id'],
            'technician_id' => $updatedData_1['technician_id'],
        ]);

        $driver_2 = Driver::factory()->create(['heavy_license' => '1', 'heavy_license_type' => 'Mercadorias']);
        User::find($driver_2->user_id)->update(['status' => 'Disponível']);

        //2 -> heavy Passangers vehicle with heavy Goods license     
        $updatedData_2 = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '1', 'heavy_type' => 'Passageiros', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $driver_2->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), [
                'expected_begin_date' => $updatedData_2['expected_begin_date'],
                'expected_end_date' => $updatedData_2['expected_end_date'],
                'trajectory' => $updatedData_2['trajectory'],
                'order_type' => $updatedData_2['order_type'],
                'vehicle_id' => $updatedData_2['vehicle_id'],
                'driver_id' => $updatedData_2['driver_id'],
                'technician_id' => $updatedData_2['technician_id'],
            ]);

        $response->assertSessionHasErrors(['driver_id']);

        $this->assertDatabaseMissing('orders', $updatedData_2);
    }

    // User is not a technician
    public function test_order_edit_fails_on_wrong_technician(): void
    {
        $order = Order::factory()->create();

        $trajectory = $this->generateRandomTrajectory();
    
        $updatedData = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'places_changed' => false,
            'places' => [],

            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => User::factory()->create(['user_type' => Arr::random(['Gestor', 'Condutor', 'Administrador']), 'status' => 'Disponível'])->id,
        ];
        
        $response = $this
            ->actingAs($this->user)
            ->put(route('orders.edit', $order->id), $updatedData);

        $response->assertSessionHasErrors(['technician_id']);

        $this->assertDatabaseMissing('orders', [
            'expected_begin_date' => $updatedData['expected_begin_date'],
            'expected_end_date' => $updatedData['expected_end_date'],
            'expected_time' => $updatedData['expected_time'],
            'distance' => $updatedData['distance'],
            'trajectory' => $updatedData['trajectory'],
            'order_type' => $updatedData['order_type'],
            'vehicle_id' => $updatedData['vehicle_id'],
            'driver_id' => $updatedData['driver_id'],
            'technician_id' => $updatedData['technician_id'],
        ]);
    }

    public function test_user_can_delete_an_order(): void
    {
        $order = Order::factory()->create();

        $this->assertDatabaseHas('order_stops', ['order_id' => $order->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/orders/delete/{$order->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseMissing('order_stops', ['order_id' => $order->id]);
    }

    public function test_manager_can_approve_a_order(): void
    {
        $manager = ManagerFactory::new()->create();

        $order = Order::factory()->create([
            'approved_date' => null,
            'manager_id' => null,
            'status' => 'Por aprovar'
        ]);

        $this->actingAs($manager);      //if intelephense shows error -> ignore it, the line is correct

        $response = $this->patch(route('orders.approve', $order), [
            'manager_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'manager_id' => $manager->id,
            'status' => 'Aprovado'
        ]);

        $response->assertRedirect(route('orders.index'));      
    }

    public function test_approve_order_fails_with_invalid_manager_id(): void
    {
        $notManager = User::factory()->create([
            'user_type' => Arr::random(['Condutor','Técnico','Nenhum']),
        ]);

        $order = Order::factory()->create([
            'approved_date' => null,
            'manager_id' => null,
            'status' => 'Por aprovar'
        ]);
        
        $this->actingAs($notManager);      //if intelephense shows error -> ignore it, the line is correct

        $response = $this->patch(route('orders.approve', $order), [
            'manager_id' => $notManager->id,
        ]);

        $response->assertSessionHasErrors(['manager_id']);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
            'manager_id' => $notManager->id,
            'status' => 'Aprovado'
        ]);

        $this->assertNull($order->fresh()->approved_date);
        $this->assertNull($order->fresh()->manager_id);
    }

    public function test_manager_can_remove_order_approval(): void
    {
        $manager = ManagerFactory::new()->create();

        $order = Order::factory()->create([
            'approved_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31'),
            'manager_id' => $manager->id,
        ]);

        $this->actingAs($manager);      //if intelephense shows error -> ignore it, the line is correct

        $response = $this->patch(route('orders.unapprove', $order), [
            'manager_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'manager_id' => null,
            'approved_date' => null,
            'status' => 'Por aprovar',
        ]);

        $response->assertRedirect(route('orders.index'));      
    }

    public function test_remove_order_approval_fails_with_invalid_manager_id(): void
    {
        $manager = ManagerFactory::new()->create();
        $notManager = User::factory()->create([
            'user_type' => Arr::random(['Condutor','Técnico','Nenhum']),
        ]);

        $order = Order::factory()->create([
            'approved_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31'),
            'manager_id' => $manager->id,
            'status' => 'Aprovado'
        ]);
        
        $this->actingAs($notManager);      //if intelephense shows error -> ignore it, the line is correct

        $response = $this->patch(route('orders.unapprove', $order), [
            'manager_id' => $notManager->id,
        ]);

        $response->assertSessionHasErrors(['manager_id']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'manager_id' => $manager->id,
            'status' => 'Aprovado',
        ]);

        $this->assertNotNull($order->fresh()->approved_date);
    }

    public function test_marks_order_as_started_successfully()
    {
        Auth::login($this->user);

        // Arrange: Create a mock order
        $order = Order::factory()->create();

        // Act: Send a POST request to the orderStarted route
        $response = $this->patch(route('orders.start', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'actual_begin_date' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_marks_order_as_ended_successfully()
    {
        Auth::login($this->user);

        // Arrange: Create a mock order
        $order = Order::factory()->create();

        // Act: Send a POST request to the orderStarted route
        $response = $this->patch(route('orders.end', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'actual_end_date' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_order_creation_handles_exception()
    {
        $withKids = fake()->boolean();
        $orderType = $withKids ? 'Transporte de Crianças' : Arr::random(['Transporte de Pessoal','Transporte de Mercadorias', 'Outros']);
        $placesData = $this->generateRandomPlacesAndKids($withKids);

        $trajectory = $this->generateRandomTrajectory();

        $data = [
            'expected_begin_date' => fake()->dateTimeBetween('2024-01-01', '2024-12-31')->format('Y-m-d H:i:s'),
            'expected_end_date' => fake()->dateTimeBetween('2025-01-01', '2025-12-31')->format('Y-m-d H:i:s'),
            'expected_time' => rand(1,200),
            'distance' => rand(1,200),
            'trajectory' => json_encode($trajectory),
            'order_type' => $orderType,
            'places' => $placesData,
            
            'vehicle_id' => Vehicle::factory()->create(['heavy_vehicle' => '0', 'wheelchair_adapted' => '1', 'status' => 'Disponível'])->id,
            'driver_id' => $this->driver->user_id,
            'technician_id' => TechnicianFactory::new()->create(['status' => 'Disponível'])->id,
        ];        

        // Mock the Order model to throw an exception
        $this->mock(Order::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // Act: Send a POST request to the create order route
        $response = $this
            ->actingAs($this->user)
            ->post(route('orders.create'), $data);

        // Assert: Check if the catch block was executed
        $response->assertRedirect(route('orders.index'));
    }
}
