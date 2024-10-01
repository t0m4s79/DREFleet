<?php

namespace App\Rules;

use Closure;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderDriverLicenseValidation implements ValidationRule
{
    protected $vehicleId;

    public function __construct($vehicleId)
    {
        $this->vehicleId = $vehicleId;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $vehicle = Vehicle::find($this->vehicleId);
        $driver = Driver::find($value);

        if ($vehicle && $vehicle->heavy_vehicle == '1') {
            if ($driver && $driver->heavy_license == '0') {
                $fail("Condutor não tem carta de pesados para este veículo");
            } elseif ($vehicle->heavy_type == 'Passageiros' && $driver->heavy_license_type == 'Mercadorias') {
                $fail("Condutor só tem carta de pesados de mercadorias");
            }
        }
    }
}
