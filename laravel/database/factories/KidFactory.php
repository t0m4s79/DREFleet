<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\KidEmail;
use App\Models\KidPhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kid>
 */
class KidFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wheelchair' => fake()->boolean(),
            'name' => fake()->name(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Kid $kid) {

            for ($i = 0; $i < rand(1, 3); $i++) {
                KidEmail::factory()->create([
                    'kid_id' => $kid->id,
                ]);
            }

            for ($i = 0; $i < rand(1, 3); $i++) {
                KidPhoneNumber::factory()->create([
                    'kid_id' => $kid->id,
                ]);
            }
        });
    }
}
