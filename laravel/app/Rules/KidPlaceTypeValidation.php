<?php

namespace App\Rules;

use Closure;
use App\Models\Place;
use Illuminate\Contracts\Validation\ValidationRule;

class KidPlaceTypeValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($value as $placeId) {
            $place = Place::find($placeId);
            if (!$place || $place->place_type !== 'Residência') {
                $fail('Apenas moradas com tipo "Residência" podem ser associadas a crianças');
            }
        }
    }
}
