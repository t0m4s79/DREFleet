<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $heavy_vehicle = fake()->boolean();;
        $heavy_type = $heavy_vehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        return [
            'make' => Arr::random(['Ford','Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(111111,999999),
            'year' => rand(1960,2024),
            'heavy_vehicle' => $heavy_vehicle,
            'heavy_type' => $heavy_type,
            'wheelchair_adapted' => fake()->boolean(),
            'wheelchair_certified' => fake()->boolean(),
            'capacity' => rand(5,15),
            'fuel_consumption' => rand(2,10),
            'status' => Arr::random(['Disponível','Indisponível', 'Em manutenção', 'Escondido']),
            'current_month_fuel_requests' => rand(0,6),
            'fuel_type' => Arr::random(['Gasóleo','Gasolina 95','Gasolina 98','Híbrido','Elétrico']),
            'current_kilometrage' => rand(1,200000),
        ];
    }
}
