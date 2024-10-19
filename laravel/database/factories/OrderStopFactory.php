<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderStop>
 */
class OrderStopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Order id and place id need to be passed explicitily when using this factory
        return [
            'stop_number' => rand(1,20),
            'expected_arrival_date' => fake()->date(),
            'time_from_previous_stop' => rand(300,2000),
            'distance_from_previous_stop' => rand(100,2000),
            //'order_id' => Order::factory(),
            //'place_id' => Place::factory(),
        ];
    }
}
