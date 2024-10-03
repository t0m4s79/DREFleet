<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleDocument>
 */
class VehicleDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween(now()->subYears(2), now());
        $expirationDate = (clone $issueDate)->modify('+1 year');

        $expired = $expirationDate < now() ? 1 : 0;

        return [
            'name' => Arr::random(["Seguro", "Documento único", "Ficha de Inspeção"]),
            'issue_date' => $issueDate,
            'expiration_date' => $expirationDate,
            'expired' => $expired,
            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
