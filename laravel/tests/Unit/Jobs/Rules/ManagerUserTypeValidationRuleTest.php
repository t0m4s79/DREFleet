<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Rules\ManagerUserTypeValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManagerUserTypeValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_the_user_is_not_a_gestor()
    {
        // Create a user who is not a 'Gestor'
        $user = User::factory()->create(['user_type' => 'Condutor']);

        $rule = new ManagerUserTypeValidation();

        $rule->validate('manager_id', $user->id, function ($message) use ($user) {
            $this->assertEquals('O utilizador com id ' . $user->id . ' selecionado não está autorizado a aprovar pedidos', $message);
        });
    }

    public function test_fails_if_the_user_does_not_exist()
    {
        $nonExistentUserId = 999; // ID that does not exist
        $rule = new ManagerUserTypeValidation();

        $rule->validate('manager_id', $nonExistentUserId, function ($message) use ($nonExistentUserId) {
            $this->assertEquals('O utilizador com id ' . $nonExistentUserId . ' selecionado não está autorizado a aprovar pedidos', $message);
        });
    }

    public function test_passes_if_the_user_is_a_gestor()
    {
        // Create a user who is a 'Gestor'
        $user = User::factory()->create(['user_type' => 'Gestor']);

        $rule = new ManagerUserTypeValidation();

        $rule->validate('manager_id', $user->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
