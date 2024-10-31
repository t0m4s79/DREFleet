<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Driver;
use App\Rules\KidDriverValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KidDriverValidationRuleTest extends TestCase
{
    public function test_fails_when_order_type_is_transporte_de_criancas_and_driver_has_no_tcc()
    {
        $driver = Driver::factory()->create(['tcc' => '0']);
        
        $rule = new KidDriverValidation('Transporte de Crianças');
        $data = ['driver_id' => $driver->user_id];
        
        $validator = Validator::make($data, ['driver_id' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Este condutor não tem tcc válido para transporte de crianças', $validator->errors()->first('driver_id'));
    }

    public function test_fails_when_order_type_is_transporte_de_criancas_and_tcc_is_expired()
    {
        $driver = Driver::factory()->create([
            'tcc' => '1',
            'tcc_expiration_date' => now()->subDays(1),
        ]);

        $rule = new KidDriverValidation('Transporte de Crianças');
        $data = ['driver_id' => $driver->user_id];

        $validator = Validator::make($data, ['driver_id' => $rule]);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Este condutor não tem tcc válido para transporte de crianças', $validator->errors()->first('driver_id'));
    }

    public function test_passes_when_order_type_is_transporte_de_criancas_and_tcc_is_valid()
    {
        $driver = Driver::factory()->create([
            'tcc' => '1',
            'tcc_expiration_date' => now()->addDays(10),
        ]);

        $rule = new KidDriverValidation('Transporte de Crianças');
        $data = ['driver_id' => $driver->user_id];

        $validator = Validator::make($data, ['driver_id' => $rule]);

        $this->assertTrue($validator->passes());
    }

    public function test_passes_when_order_type_is_not_transporte_de_criancas()
    {
        $driver = Driver::factory()->create(['tcc' => '0']);

        $rule = new KidDriverValidation('Outra Ordem');
        $data = ['driver_id' => $driver->user_id];

        $validator = Validator::make($data, ['driver_id' => $rule]);

        $this->assertTrue($validator->passes());
    }
}
