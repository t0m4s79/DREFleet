<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Rules\RoleUserTypeValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleUserTypeValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_if_the_user_does_not_exist()
    {
        $nonExistentUserId = -1; // ID that does not exist
        $rule = new RoleUserTypeValidation();

        $rule->validate('user_id', $nonExistentUserId, function ($message) {
            $this->assertEquals('Somente utilizadores de tipo "Nenhum" podem ser convertidos em condutores', $message);
        });
    }

    public function test_fails_if_the_user_type_is_not_nenhum()
    {
        // Create a user who is not of type 'Nenhum'
        $user = User::factory()->create(['user_type' => 'Condutor']);

        $rule = new RoleUserTypeValidation();

        $rule->validate('user_id', $user->id, function ($message) {
            $this->assertEquals('Somente utilizadores de tipo "Nenhum" podem ser convertidos em condutores', $message);
        });
    }

    public function test_passes_if_the_user_type_is_nenhum()
    {
        // Create a user who is of type 'Nenhum'
        $user = User::factory()->create(['user_type' => 'Nenhum']);

        $rule = new RoleUserTypeValidation();

        $rule->validate('user_id', $user->id, function ($message) {
            $this->fail('The validation should have passed.');
        });

        $this->assertTrue(true); // If no exception is thrown, the test passes.
    }
}
