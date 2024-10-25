<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class RoleUserTypeValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);
        
        if (!$user || $user->user_type != 'Nenhum') {
            $fail('Somente utilizadores de tipo "Nenhum" podem ser convertidos em condutores');
        }
    }
}
