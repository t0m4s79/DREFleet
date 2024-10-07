<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\OrderRoute;
use App\Models\Vehicle;
use App\Models\OrderStop;
use Illuminate\Support\Arr;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $technician = TechnicianFactory::new()->create();
        $vehicle = Vehicle::factory()->create();
        $driver = Driver::factory()->create();
        
        $trajectory = $this->generateRandomTrajectory();

        // Randomly decide if the order is approved
        $isApproved = fake()->boolean();

        // Randomly decide if the order has a defined route
        $hasRoute = fake()->boolean();

        // If approved, generate an approved date and assign a manager
        $approved_date = $isApproved ? fake()->dateTimeBetween('2024-01-01', '2025-12-31') : null;
        $manager = $isApproved ? ManagerFactory::new()->create() : null;

        $route = $hasRoute? OrderRouteFactory::new()->create() : null;

        $beginDate = fake()->dateTimeBetween(now()->subYear(), now()->addYear());
        $endDate = (clone $beginDate)->modify('+1 day');

        // Future Order
        if ($beginDate > now()) {
            $status = Arr::random(['Por aprovar', 'Cancelado/Não aprovado', 'Aprovado']);

        // Past Order
        } else if (now() > $endDate) {
            $status = Arr::random(['Cancelado/Não aprovado', 'Finalizado', 'Interrompido']);

        // Current Order
        } else {
            $status = 'Em curso';
        }

        return [
            'expected_begin_date' => $beginDate,
            'expected_end_date' => $endDate,
            'expected_time' => 86400,           //seconds in a day
            'distance' => rand(1000,20000),
            'status' => $status,
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->user_id,
            'technician_id' => $technician->id,
            'order_route_id' => $hasRoute ? $route->id : null,

            'approved_date' => $approved_date ? $approved_date : null,
            'manager_id' => $manager ? $manager->id : null,
        ];
    }

    private function generateRandomTrajectory()
    {
        $boundsSouthWestCorner = [32.269181, -17.735033];
        $boundsNorthEastCorner = [33.350247, -15.861279];

        // Generate random start and end points within the bounds
        $startLat = fake()->latitude($boundsSouthWestCorner[0], $boundsNorthEastCorner[0]);
        $startLng = fake()->longitude($boundsSouthWestCorner[1], $boundsNorthEastCorner[1]);

        $endLat = fake()->latitude($boundsSouthWestCorner[0], $boundsNorthEastCorner[0]);
        $endLng = fake()->longitude($boundsSouthWestCorner[1], $boundsNorthEastCorner[1]);

        $points = [];
        $numPoints = rand(2, 6); // Number of points in the trajectory

        for ($i = 0; $i <= $numPoints; $i++) {
            $lat = $startLat + ($endLat - $startLat) * ($i / $numPoints);
            $lng = $startLng + ($endLng - $startLng) * ($i / $numPoints);
            
            // Ensure the generated points are within bounds
            $lat = min(max($lat, $boundsSouthWestCorner[0]), $boundsNorthEastCorner[0]);
            $lng = min(max($lng, $boundsSouthWestCorner[1]), $boundsNorthEastCorner[1]);
            
            $points[] = [
                'lat' => $lat,
                'lng' => $lng,
            ];
    }

    return $points;
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $trajectory = json_decode($order->trajectory, true);
            $totalPoints = count($trajectory);
            $stopAverageTime = (int) (86400 / $totalPoints);
            $stopAverageDistance = (int) ($order->distance / $totalPoints);

            $stopNumber = 0;
            // Create stops for each point
            foreach ($trajectory as $point) {
                $stopNumber++;
                $lat = $point['lat'];
                $lng = $point['lng'];

                $coordinates = new Point($lng, $lat);

                $place = Place::factory()->create([
                    'coordinates' => $coordinates,
                ]);

                // Create the order stop associated with this order
                $orderStop = OrderStop::factory()->create([
                    'order_id' => $order->id,
                    'place_id' => $place->id,
                    'stop_number' => $stopNumber,
                    'time_from_previous_stop' => $stopAverageTime,
                    'distance_from_previous_stop' => $stopAverageDistance,
                ]);

                if (rand(0, 1) === 1) {
                    $place->update([
                        'place_type' => 'Residência', // Replace 'some_attribute' with the actual attribute you want to update
                    ]);
                    $kid = Kid::factory()->create();
                    $kid->places()->attach($place->id);
                    $kid->orderStops()->attach($orderStop->id, ['place_id' => $place->id]);
                }
            }
        });
    }
}
