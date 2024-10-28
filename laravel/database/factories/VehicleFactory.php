<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use App\Models\VehicleDocument;
use App\Models\VehicleAccessory;
use App\Models\VehicleKilometrageReport;
use App\Models\VehicleRefuelRequest;
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
            $date = \Carbon\Carbon::parse(fake()->dateTimeBetween(now()->subDays(20), now()->subDays(10)));
        
            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Seguro',
            ]);
        
            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Inspeção',
            ]);
        
            VehicleDocument::factory()->create([
                'vehicle_id' => $vehicle->id,
                'name' => 'Documento Único',
            ]);
        
            for ($i = 0; $i < rand(1, 3); $i++) {
                VehicleAccessory::factory()->create([
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        
            for ($i = 0; $i < rand(1, 5); $i++) {
                $date = $date->addDay();

                // Check if there are any drivers in the database, otherwise create one
                $driver =  Driver::inRandomOrder()->first() ?? Driver::factory()->create();
                
                VehicleKilometrageReport::factory()->create([
                    'date' => $date,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->user_id,
                ]);
            }

            for ($i= 1; $i <= $vehicle->current_month_fuel_requests; $i++) {
                
                if ($i <= 4) {
                    $requestType = 'Normal';
                } else if ($i <= 10) {
                    $requestType = 'Especial';
                } else {
                    $requestType = 'Excepcional';
                }

                VehicleRefuelRequest::factory()->create([
                    'vehicle_id' => $vehicle->id,
                    'fuel_type' => $vehicle->fuel_type == 'Híbrido' ? 'Elétrico' : $vehicle->fuel_type,
                    'monthly_request_number' => $i,
                    'request_type' => $requestType,
                ]);
            }
        });
    }
}
