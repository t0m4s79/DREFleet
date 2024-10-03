<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class TechnicianUserTypeValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);
        
        if (!$user || $user->user_type !== 'Técnico') {
            $fail('O valor selecionado para o campo do técnico é inválido');
        }
    }
}
