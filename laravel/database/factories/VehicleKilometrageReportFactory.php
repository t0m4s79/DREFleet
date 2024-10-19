<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleKilometrageReport>
 */
class VehicleKilometrageReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kilometrage = rand(10000,100000);
        
        // Vehicle id and driver id need to be passed explicitily when using this factory
        return [
            'date' => fake()->dateTimeBetween(now()->subDays(10), now()),
            'begin_kilometrage' => $kilometrage,
            'end_kilometrage' => $kilometrage + rand(100,1000),
            //'vehicle_id' => Vehicle::factory(),
            //'driver_id' => Driver::factory(),
        ];
    }
}
