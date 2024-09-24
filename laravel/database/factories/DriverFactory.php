<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Arr;
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
        $heavyLicense = fake()->boolean();
        $heavyLicenseType = $heavyLicense ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        return [
            'user_id' => User::factory()->state([
                'user_type' => 'Condutor',
            ]),
            'heavy_license' => $heavyLicense,
            'heavy_license_type' => $heavyLicenseType,
        ];    
    }
}
