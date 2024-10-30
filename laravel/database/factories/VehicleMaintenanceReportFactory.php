<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleMaintenanceReport>
 */
class VehicleMaintenanceReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween(now()->subYears(2), now());
        $endDate = Carbon::parse($date)->addDays(rand(1,10));
        $kilometrage = null;

        // Future
        if ($date > now()) {
            $status = 'Agendado';

        // Past
        } else if (now() > $endDate) {
            $status = 'Finalizado';
            $kilometrage = rand(10000, 200000);

        // Current
        } else {
            $status = 'A decorrer';
            $kilometrage = rand(10000, 200000);
        }

        // Vehicle id needs to be passed explicitily when using this factory
        return [
            'begin_date' => $date,
            'end_date' => $endDate,
            'type' => Arr::random(['Manutenção', 'Anomalia', 'Reparação', 'Outros']),
            'description' => fake()->sentence(),
            'kilometrage' => $kilometrage,
            'total_cost' => fake()->randomFloat(2, 10, 200),
            'items_cost' => $this->generateItemsCost(),
            'service_provider' => null,
            'status' => $status,
            //'vehicle_id' => Vehicle::factory(),
        ];
    }

    private function generateItemsCost()
    {
        $items = [
            'pneus (' . rand(1, 4) . ')' => rand(50, 150),
            'óleo' => rand(10, 30),
            'filtro de ar' => rand(15, 25),
            'troca de pastilhas de travão' => rand(20, 60),
            'alinhamento de direção' => rand(30, 70),
            'revisão geral' => rand(100, 200),
            'troca de bateria' => rand(80, 120),
            'fluido de travão' => rand(10, 20),
            'troca de amortecedores' => rand(50, 100),
            'balanceamento de rodas' => rand(20, 40),
            'troca de filtro de óleo' => rand(10, 25),
            'inspeção de suspensão' => rand(30, 80),
        ];
    
        // Retrieve a random subset of items as associative array
        $selectedItemsKeys = array_rand($items, rand(2, count($items)));
        
        // Ensure $selectedItemsKeys is an array in case only one item is selected
        if (!is_array($selectedItemsKeys)) {
            $selectedItemsKeys = [$selectedItemsKeys];
        }
        
        // Get the selected items with original keys
        $selectedItems = [];
        foreach ($selectedItemsKeys as $key) {
            $selectedItems[$key] = $items[$key];
        }
    
        return $selectedItems;
    }
}
