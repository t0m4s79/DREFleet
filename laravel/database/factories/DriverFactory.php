<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //USER_ID ALWAYS 0 ON DRIVER FACTORY CREATION (WHEN TESTS AND OTHER FACTORIES CALL THIS)????
        return [
            'user_id' => User::factory()->state([
                'user_type' => 'Condutor',
            ]),
            'heavy_license' => fake()->boolean(),
        ];    
    }
}
