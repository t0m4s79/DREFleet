<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleAccessory>
 */
class VehicleAccessoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expirationDate = rand(0, 1) ? fake()->dateTimeBetween(now()->subYear(), now()->addYear()) : null;

        if ($expirationDate && $expirationDate < now()) {
            $name = "Kit de 1ºs socorros";
            $condition = 'Expirado';
        } elseif ($expirationDate) {
            $name = "Kit de 1ºs socorros";
            $condition = Arr::random(['Danificado', 'Aceitável']);
        } else {
            $name = Arr::random(["Colete", "Triângulo", "Pneu Sobresselente", "Macaco"]);
            $condition = Arr::random(['Danificado', 'Aceitável']);
        }
    
        // Vehicle id needs to be passed explicitily when using this factory
        return [
            'name' => $name,
            'expiration_date' => $expirationDate,
            'condition' => $condition,
            //'vehicle_id' => Vehicle::factory(),
        ];
    }
}
