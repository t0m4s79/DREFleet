<?php

namespace App\Rules;

use Closure;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderVehicleCapacityValidation implements ValidationRule
{
    protected $totalPassengers;
    protected $orderType;

    public function __construct($totalPassengers, $orderType)
    {
        $this->totalPassengers = $totalPassengers;
        $this->orderType = $orderType;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $vehicle = Vehicle::find($value);

        if ($vehicle && $this->orderType === 'Transporte de Crianças') {
            if ($this->totalPassengers > $vehicle->capacity) {
                $fail("O número de crianças + técnico excede a capacidade do veículo");
            }
        }
    }
}