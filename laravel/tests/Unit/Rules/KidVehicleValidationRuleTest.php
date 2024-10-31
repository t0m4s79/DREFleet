<?php

namespace Tests\Feature;

use App\Models\Kid;
use Tests\TestCase;
use App\Models\Vehicle;
use App\Rules\KidVehicleValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidVehicleValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_the_order_type_is_not_transporte_de_criancas()
    {
        $vehicle = Vehicle::factory()->create(['wheelchair_adapted' => true, 'tcc' => 1]);
        $vehicleId = $vehicle->id;

        // Create a kid
        $kid = Kid::factory()->create();
        
        $rule = new KidVehicleValidation('Transporte de Mercadorias', $vehicleId);

        $rule->validate('kid', $kid->id, function ($message) {
            $this->assertEquals('Crianças não podem ser incluídas a menos que o tipo de pedido seja "Transporte de Crianças"', $message);
        });
    }

    public function test_fails_if_vehicle_is_not_tcc_certified()
    {
        $vehicle = Vehicle::factory()->create(['wheelchair_adapted' => true, 'tcc' => 0]);
        $vehicleId = $vehicle->id;

        // Create a kid
        $kid = Kid::factory()->create();
        
        $rule = new KidVehicleValidation('Transporte de Crianças', $vehicleId);

        $rule->validate('kid', $kid->id, function ($message) {
            $this->assertEquals('Este veículo não tem certificado de transporte coletivo de crianças (tcc)', $message);
        });
    }

    public function test_fails_if_the_vehicle_is_not_wheelchair_adapted_but_kid_requires_one()
    {
        // Create a kid who requires a wheelchair
        $kid = Kid::factory()->create(['wheelchair' => true]);
        
        // Create a vehicle that is not wheelchair-adapted
        $vehicle = Vehicle::factory()->create(['wheelchair_adapted' => false, 'tcc' => 1]);

        $rule = new KidVehicleValidation('Transporte de Crianças', $vehicle->id);

        $rule->validate('kid', $kid->id, function ($message) {
            $this->assertEquals("Este veículo não está preparado para transportar crianças com cadeira de rodas", $message);
        });
    }

    public function test_passes_if_the_order_type_is_transporte_de_criancas_and_vehicle_is_wheelchair_adapted_and_has_tcc()
    {
        // Create a kid who requires a wheelchair
        $kid = Kid::factory()->create(['wheelchair' => true]);

        // Create a vehicle that is wheelchair-adapted
        $vehicle = Vehicle::factory()->create(['wheelchair_adapted' => true, 'tcc' => 1]);

        $rule = new KidVehicleValidation('Transporte de Crianças', $vehicle->id);

        $rule->validate('kid', $kid->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }

    public function test_passes_if_the_kid_does_not_require_a_wheelchair()
    {
        // Create a kid who does not require a wheelchair
        $kid = Kid::factory()->create(['wheelchair' => false]);

        // Create a vehicle (wheelchair_adapted status is irrelevant here)
        $vehicle = Vehicle::factory()->create(['wheelchair_adapted' => false, 'tcc' => 1]);

        $rule = new KidVehicleValidation('Transporte de Crianças', $vehicle->id);

        $rule->validate('kid', $kid->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
