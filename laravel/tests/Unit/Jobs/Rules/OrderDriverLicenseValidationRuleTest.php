<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Rules\OrderDriverLicenseValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderDriverLicenseValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vehicle = Vehicle::factory()->create(['heavy_vehicle' => '1']);
    }

    public function test_fails_if_the_driver_does_not_have_a_heavy_license()
    {
        // Create a driver without a heavy license
        $driver = Driver::factory()->create(['heavy_license' => '0']);

        $rule = new OrderDriverLicenseValidation($this->vehicle->id);

        $rule->validate('driver_id', $driver->user_id, function ($message) {
            $this->assertEquals("Condutor não tem carta de pesados para este veículo", $message);
        });
    }

    public function test_fails_if_the_driver_has_a_heavy_license_of_wrong_type()
    {
        // Create a driver with a heavy license for "Mercadorias"
        $driver = Driver::factory()->create([
            'heavy_license' => '1',
            'heavy_license_type' => 'Mercadorias'
        ]);

        // Set the vehicle to require a heavy license for "Passageiros"
        $this->vehicle->update(['heavy_type' => 'Passageiros']);

        $rule = new OrderDriverLicenseValidation($this->vehicle->id);

        $rule->validate('driver_id', $driver->user_id, function ($message) {
            $this->assertEquals("Condutor só tem carta de pesados de mercadorias", $message);
        });
    }

    public function test_passes_if_the_driver_has_the_correct_heavy_license()
    {
        // Create a driver with a heavy license for "Passageiros"
        $driver = Driver::factory()->create([
            'heavy_license' => '1',
            'heavy_license_type' => 'Passageiros'
        ]);

        $rule = new OrderDriverLicenseValidation($this->vehicle->id);

        $rule->validate('driver_id', $driver->user_id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
