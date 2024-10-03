<?php

namespace App\Rules;

use Closure;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderVehicleCapacityValidation implements ValidationRule
{
    protected $orderType;
    protected $vehicleId;
    protected $places;
    protected $technicianId;

    public function __construct($orderType, $vehicleId, $places, $technicianId)
    {
        $this->orderType = $orderType;
        $this->vehicleId = $vehicleId;
        $this->places = $places;
        $this->technicianId = $technicianId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $vehicle = Vehicle::find($this->vehicleId);

        if ($vehicle && $this->orderType === 'Transporte de Crianças') {
            $kids = collect($this->places)->filter(function ($place) {
                return isset($place['kid_id']);
            });

            // Count kids and technician
            $totalPeople = $kids->count() + ($this->technicianId ? 1 : 0);

            // Check if total people exceed the vehicle capacity
            if ($totalPeople > $vehicle->capacity) {
                $fail("O número de crianças + técnico excede a capacidade do veículo");
            }
        }
    }
}
