<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Rules\VehicleDocumentDataValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleDocumentDataValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_passes_if_value_is_null()
    {
        $rule = new VehicleDocumentDataValidation();

        $rule->validate('document_data', null, function ($message) {
            $this->fail('Validation should pass for null value.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }

    public function test_fails_if_key_is_empty_and_value_is_not_empty()
    {
        $data = ['' => 'Valid Value']; // Empty key
        $rule = new VehicleDocumentDataValidation();

        $rule->validate('document_data', $data, function ($message) {
            $this->assertEquals('Os campos dos dados adicionais não podem estar vazios.', $message);
        });
    }

    public function test_fails_if_value_is_empty_and_key_is_not_empty()
    {
        $data = ['Valid Key' => '']; // Empty value
        $rule = new VehicleDocumentDataValidation();

        $rule->validate('document_data', $data, function ($message) {
            $this->assertEquals('Os campos dos dados adicionais não podem estar vazios.', $message);
        });
    }

    public function test_passes_if_all_keys_and_values_are_valid()
    {
        $data = ['Valid Key 1' => 'Value 1', 'Valid Key 2' => 'Value 2'];
        $rule = new VehicleDocumentDataValidation();

        $rule->validate('document_data', $data, function ($message) {
            $this->fail('Validation should pass for valid keys and values.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
