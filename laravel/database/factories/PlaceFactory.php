<?php

namespace Database\Factories;

use App\Models\Kid;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address' => fake()->address(),
            'known_as' =>  Arr::random(['Casa do Avô','Casa da Tia', 'Casa do Pai', 'Casa da Mãe','Restaurante da Mãe','Casa do Primo', 'Café da Tia', 'Restaurante do Tio','Casa']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'kid_id' => Kid::factory(),
        ];
    }
}
