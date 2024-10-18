<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleDocumentDataValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Return early if the value is null
        if ($value === null) {
            return; // No validation needed for null
        }

        // Get all keys from the array
        $keys = array_keys($value);

        // Check for empty keys or values
        foreach ($keys as $key) {
            // Check if the key or its corresponding value is an empty string
            if (trim($key) === '' && trim($value[$key]) !== '' || trim($key) !== '' && trim($value[$key]) === '') {
                $fail('Os campos dos dados adicionais não podem estar vazios.');
                return; // Exit after the first failure
            }
        }
    }
}
