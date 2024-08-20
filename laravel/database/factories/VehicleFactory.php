<?php

namespace Database\Factories;

use Faker\Guesser\Name;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Nette\Utils\Strings;
use PharIo\Manifest\License;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'make' => Arr::random(['Ford','Reanult', 'VW', 'Fiat']),
            'model' => fake()->name(),
            'license_plate' => rand(111111,999999),
            'heavy_vehicle' => rand(0,1),
            'wheelchair_adapted' => rand(0,1),
            'capacity' => rand(5,15),
            'fuel_consumption' => rand(2,10),
            'status_code' => rand(0,1),
            'current_month_fuel_requests' => rand(0,6)
        ];
    }
}
