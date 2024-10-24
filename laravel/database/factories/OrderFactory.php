<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\User;
use App\Models\Order;
use App\Models\Place;
use App\Models\Driver;
use App\Models\OrderOccurrence;
use App\Models\Vehicle;
use App\Models\OrderStop;
use App\Models\OrderRoute;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
        $trajectory = $this->generateRandomTrajectory();

        $orderTime = rand(2000,86400);
        $beginDate = fake()->dateTimeBetween(now()->subYear(), now()->addYear());
        $endDate = Carbon::parse($beginDate)->addSeconds($orderTime);

        // Future Order
        if ($beginDate > now()) {
            $status = Arr::random(['Por aprovar', 'Cancelado/Não aprovado', 'Aprovado']);

        // Past Order
        } else if (now() > $endDate) {
            $status = Arr::random(['Cancelado/Não aprovado', 'Finalizado', 'Interrompido']);
            $actualBeginDate = Carbon::parse($beginDate)->addSeconds(rand(-3600, 3600));
            $actualEndDate = Carbon::parse($endDate)->addSeconds(rand(-3600, 3600));

        // Current Order
        } else {
            $status = 'Em curso';
            $actualBeginDate = Carbon::parse($beginDate)->addSeconds(rand(-3600, 3600));
        }

        // Check if there are any drivers in the database, otherwise create one
        $driver =  Driver::inRandomOrder()->first() ?? Driver::factory()->create();

        // Check if there are any vehicles in the database, otherwise create one
        $vehicle = Vehicle::inRandomOrder()->first() ?? Vehicle::factory()->create();

        // Check if there are any technicians in the database, otherwise create one
        $technician = User::where('user_type', 'Técnico')->inRandomOrder()->first() ?? TechnicianFactory::new()->create();

        // Randomly decide if the order has a defined route
        if (fake()->boolean()) {
            // Check if there are any order routes in the database, otherwise create one
            $route = OrderRoute::inRandomOrder()->first() ?? OrderRouteFactory::new()->create();

        } else {
            $route = null;
        }

        // Randomly decide if the order is approved
        if (fake()->boolean()) {
            // Check if there are any managers in the database, otherwise create one
            $manager = User::where('user_type', 'Gestor')->inRandomOrder()->first() ?? ManagerFactory::new()->create();
            $approved_date = Carbon::parse($beginDate)->addDays(rand(-20,-1));

        } else {
            $approved_date = null;
            $manager = null;
        }

        return [
            'expected_begin_date' => $beginDate,
            'expected_end_date' => $endDate,
            'actual_begin_date' => $actualBeginDate ?? null,
            'actual_end_date' => $actualEndDate ?? null,
            'expected_time' => $orderTime,
            'distance' => rand(1000,20000),
            'status' => $status,
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'vehicle_id' =>  $vehicle,
            'driver_id' => $driver,
            'technician_id' => $technician,
            'order_route_id' => $route ? $route->id : null,

            'approved_date' => $approved_date ? $approved_date : null,
            'manager_id' => $manager ? $manager->id : null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $trajectory = json_decode($order->trajectory, true);
            $totalPoints = count($trajectory);
            $stopAverageTime = (int) ($order->expected_time / $totalPoints);
            $stopAverageDistance = (int) ($order->distance / $totalPoints);

            // Introduce buffers for randomness within a reasonable range
            $timeBuffer = (int) ($stopAverageTime * 0.25); // Random up to 25% of average time
            $distanceBuffer = (int) ($stopAverageDistance * 0.25); // Random up to 25% of average distance

            // Buffer for actual arrival date variance (15% of average time)
            $actualTimeVariance = (int) ($stopAverageTime * 0.15);

            if (rand(0, 1) === 1) {
                OrderOccurrence::factory()->create([
                    'order_id' => $order->id,
                ]);
            }

            $stopExpectedArrivalDate = Carbon::parse($order->expected_begin_date);

            $stopNumber = 0;
            foreach ($trajectory as $point) {
                $stopNumber++;
                $lat = $point['lat'];
                $lng = $point['lng'];

                $coordinates = new Point($lat, $lng);

                $place = Place::factory()->create([
                    'coordinates' => $coordinates,
                ]);

                // Add random variation to time and distance for each stop
                $randomTimeDeviation = rand(-$timeBuffer, $timeBuffer);
                $randomDistanceDeviation = rand(-$distanceBuffer, $distanceBuffer);

                $timeFromPreviousStop = $stopAverageTime + $randomTimeDeviation;
                $distanceFromPreviousStop = $stopAverageDistance + $randomDistanceDeviation;

                // Calculate actual arrival date with a random variation from the expected date
                $randomActualDeviation = rand(-$actualTimeVariance, $actualTimeVariance);
                $actualArrivalDate = $stopExpectedArrivalDate->copy()->addSeconds($randomActualDeviation);

                // Create the order stop associated with this order
                $orderStop = OrderStop::factory()->create([
                    'order_id' => $order->id,
                    'place_id' => $place->id,
                    'stop_number' => $stopNumber,
                    'time_from_previous_stop' => $timeFromPreviousStop,
                    'distance_from_previous_stop' => $distanceFromPreviousStop,
                    'expected_arrival_date' => $stopExpectedArrivalDate,
                    'actual_arrival_date' => $actualArrivalDate, // Add actual arrival date
                ]);

                // Move the expected arrival date forward by the adjusted time
                $stopExpectedArrivalDate = $stopExpectedArrivalDate->addSeconds($timeFromPreviousStop);

                if (rand(0, 1) === 1) {
                    $place->update([
                        'place_type' => 'Residência',
                    ]);
                    $kid = Kid::factory()->create();
                    $kid->places()->attach($place->id);
                    $kid->orderStops()->attach($orderStop->id, ['place_id' => $place->id]);
                }
            }
        });
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
}
