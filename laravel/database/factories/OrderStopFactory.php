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
        return [
            'stop_number' => rand(1,20),
            'planned_arrival_date' => null,
            'order_id' => Order::factory(),
            'place_id' => Place::factory(),
        ];
    }
}
