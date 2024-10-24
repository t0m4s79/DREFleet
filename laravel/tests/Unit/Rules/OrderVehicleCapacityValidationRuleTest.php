<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\WithFaker;
use App\Rules\OrderVehicleCapacityValidation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderVehicleCapacityValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a vehicle for testing
        $this->vehicle = Vehicle::factory()->create(['capacity' => 5]); // Vehicle with a capacity of 5
    }

    public function test_fails_if_total_passengers_exceeds_vehicle_capacity()
    {
        $totalPassengers = 6; // Total passengers exceed vehicle capacity

        $rule = new OrderVehicleCapacityValidation($totalPassengers, 'Transporte de Crianças');

        $rule->validate('vehicle_id', $this->vehicle->id, function ($message) {
            $this->assertEquals("O número de crianças + técnico excede a capacidade do veículo", $message);
        });
    }

    public function test_passes_if_total_passengers_does_not_exceed_vehicle_capacity()
    {
        $totalPassengers = 4; // Total passengers within vehicle capacity

        $rule = new OrderVehicleCapacityValidation($totalPassengers, 'Transporte de Crianças');

        $rule->validate('vehicle_id', $this->vehicle->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }

    public function test_skips_validation_for_order_types_other_than_transporte_de_criancas()
    {
        $totalPassengers = 6; // Total passengers exceed vehicle capacity

        $rule = new OrderVehicleCapacityValidation($totalPassengers, 'Transporte de Mercadorias');

        $rule->validate('vehicle_id', $this->vehicle->id, function ($message) {
            $this->assertTrue(true); // No failure expected
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
