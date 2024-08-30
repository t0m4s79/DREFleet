<?php

namespace Database\Factories;

use App\Models\Kid;
use Illuminate\Support\Arr;
use MatanYadaev\EloquentSpatial\Objects\Point;
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

        $latitude = fake()->latitude();
        $longitude = fake()->longitude();

        $firstPart = ['Casa', 'Restaurante', 'Bar', 'Café'];
        $secondPart = ['do Avô', 'da Avó', 'do Pai', 'da Mãe', 'do Primo', 'da Tia', 'do Tio'];

        return [
            'address' => fake()->address(),
            'known_as' => ''.Arr::random($firstPart) . ' ' . Arr::random($secondPart),
            'coordinates' => new Point($latitude, $longitude),
        ];
    }
}
