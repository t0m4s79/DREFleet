<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class ManagerUserTypeValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the manager_id belongs to a user with 'Gestor' type
        $user = User::find($value);
        if (!$user || $user->user_type !== 'Gestor') {
            $fail('O utilizador com id ' . $value . ' selecionado não está autorizado a aprovar pedidos');
        }
    }
}
