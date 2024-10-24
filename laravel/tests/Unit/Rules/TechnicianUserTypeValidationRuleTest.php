<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Rules\TechnicianUserTypeValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicianUserTypeValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_the_user_does_not_exist()
    {
        $nonExistentUserId = -1; // ID that does not exist
        $rule = new TechnicianUserTypeValidation();

        $rule->validate('user_id', $nonExistentUserId, function ($message) {
            $this->assertEquals('O valor selecionado para o campo do técnico é inválido', $message);
        });
    }

    public function test_fails_if_the_user_type_is_not_tecnico()
    {
        // Create a user who is not of type 'Técnico'
        $user = User::factory()->create(['user_type' => 'Condutor']);

        $rule = new TechnicianUserTypeValidation();

        $rule->validate('user_id', $user->id, function ($message) {
            $this->assertEquals('O valor selecionado para o campo do técnico é inválido', $message);
        });
    }

    public function test_passes_if_the_user_type_is_tecnico()
    {
        // Create a user who is of type 'Técnico'
        $user = User::factory()->create(['user_type' => 'Técnico']);

        $rule = new TechnicianUserTypeValidation();

        $rule->validate('user_id', $user->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
