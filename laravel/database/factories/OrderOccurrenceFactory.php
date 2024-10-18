<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderOccurrence>
 */
class OrderOccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Arr::random(['Manutenções', 'Reparações', 'Lavagens', 'Outros']),
            'description' => fake()->sentence(),
            'order_id' => Order::factory(),
        ];
    }
}
