<?php

namespace Database\Factories;

use App\Models\Kid;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KidEmail>
 */
class KidEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->email(),
            'owner_name' => fake()->name(),
            'relationship_to_kid' => Arr::random(['Avô', 'Avó', 'Pai', 'Mãe', 'Primo', 'Tia', 'Tio', 'Tutor']),
            'preference' => Arr::random(['Alternativa', 'Preferida']),
            'kid_id' => Kid::factory(),
        ];
    }
}
