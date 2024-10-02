<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\VehicleDocument;
use App\Models\VehicleAccessory;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $heavyVehicle = fake()->boolean();
        $heavyType = $heavyVehicle ? Arr::random(['Mercadorias', 'Passageiros']) : null;

        return [
            'make' => Arr::random(['Ford','Reanult', 'VW', 'Fiat', 'Peugeot']),
            'model' => fake()->name(),
            'license_plate' => rand(111111,999999),
            'year' => rand(1980,2024),
            'heavy_vehicle' => $heavyVehicle,
            'heavy_type' => $heavyType,
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

    public function configure()
    {
        return $this->afterCreating(function (Vehicle $vehicle) {

            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Seguro',
            ]);

            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Ficha de Inspeção',
            ]);

            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Seguro',
            ]);

            for ($i = 0; $i < rand(0, 3); $i++) {
                VehicleAccessory::factory()->create([
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        });
    }
}
