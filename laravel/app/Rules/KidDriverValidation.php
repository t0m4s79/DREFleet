<?php

namespace App\Rules;

use App\Models\Driver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KidDriverValidation implements ValidationRule
{
    protected $orderType;

    public function __construct($orderType)
    {
        $this->orderType = $orderType;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $driver = Driver::find($value);

        if ($value) {
            // Check if the order type allows kids
            if ($this->orderType == 'Transporte de Crianças') {
                if (($driver->tcc == '0' || ($driver->tcc == '1' && $driver->tcc_expiration_date < now()))) {
                    $fail('Este condutor não tem tcc válido para transporte de crianças');
                }
            }
        }
    }
}
