<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
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
        $begin_latitude = fake()->latitude();
        $begin_longitude = fake()->longitude();

        $end_latitude = fake()->latitude();
        $end_longitude = fake()->longitude();

        $technician = TechnicianFactory::new()->create();
        $vehicle = Vehicle::factory()->create();
        $driver = Driver::factory()->create();
        
        $trajectory = $this->generateTrajectory($begin_latitude, $begin_longitude, $end_latitude, $end_longitude);

        // Randomly decide if the order is approved
        $isApproved = fake()->boolean();

        // If approved, generate an approved date and assign a manager
        $approved_date = $isApproved ? fake()->dateTimeBetween('2024-01-01', '2025-12-31') : null;
        $manager = $isApproved ? ManagerFactory::new()->create() : null;

        return [
            'begin_address' => fake()->address(),
            'end_address' => fake()->address(),
            'begin_date' => fake()->dateTimeBetween('2024-01-01', '2025-12-31'),
            'end_date' => fake()->dateTimeBetween('2024-01-01','2025-12-31'),
            'begin_coordinates' => new Point($begin_latitude, $begin_longitude),
            'end_coordinates' => new Point($end_latitude, $end_longitude),
            'trajectory' => json_encode($trajectory),
            'order_type' => Arr::random(['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']),

            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->user_id,
            'technician_id' => $technician->id,

            'approved_date' => $approved_date ? $approved_date : null,
            'manager_id' => $manager ? $manager->id : null,
        ];
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
}
