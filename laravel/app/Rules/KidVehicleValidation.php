<?php

namespace App\Rules;

use Closure;
use App\Models\Kid;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class KidVehicleValidation implements ValidationRule
{
    protected $orderType;
    protected $vehicleId;

    public function __construct($orderType, $vehicleId)
    {
        $this->orderType = $orderType;
        $this->vehicleId = $vehicleId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $kid = Kid::find($value);
        $vehicle = Vehicle::find($this->vehicleId);

        if ($value) {
            // Check if the order type allows kids
            if ($kid && $this->orderType !== 'Transporte de Crianças') {
                $fail('Crianças não podem ser incluídas a menos que o tipo de pedido seja "Transporte de Crianças"');
            }

            if ($kid && !$vehicle->tcc) {
                $fail("Este veículo não tem certificado de transporte coletivo de crianças (tcc)");
            }

            // Check if the vehicle is wheelchair-adapted for kids with a wheelchair
            if ($kid && $kid->wheelchair && !$vehicle->wheelchair_adapted) {
                $fail("Este veículo não está preparado para transportar crianças com cadeira de rodas");
            }
        }
    }
}
