<?php

namespace App\Rules;

use Closure;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderVehicleAvailabilityValidation implements ValidationRule
{
    protected $newOrderStartDate;
    protected $newOrderEndDate;
    protected $currentOrderId;

    public function __construct( $newOrderStartDate, $newOrderEndDate, $currentOrderId = null)
    {
        $this->newOrderStartDate = $newOrderStartDate;
        $this->newOrderEndDate = $newOrderEndDate;
        $this->currentOrderId = $currentOrderId; // Store the current order's ID, if on edit request
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $vehicle = Vehicle::find($value);

        $vehicleOrders = Order::where('vehicle_id', $vehicle->id)
            ->when($this->currentOrderId, function ($query) {
                $query->where('id', '!=', $this->currentOrderId);
            })
            ->get();
        
        if ($vehicle->status != 'Escondido' || $vehicle->status != 'Em manutenção' || $vehicle->status != 'Indisponível') {
            foreach($vehicleOrders as $vehicleOrder)
                if (
                    // New order starts during an existing order
                    ($this->newOrderStartDate >= $vehicleOrder->expected_begin_date && $this->newOrderStartDate <= $vehicleOrder->expected_end_date) ||
                    // New order ends during an existing order
                    ($this->newOrderEndDate >= $vehicleOrder->expected_begin_date && $this->newOrderEndDate <= $vehicleOrder->expected_end_date) ||
                    // New order fully covers an existing order
                    ($this->newOrderStartDate <= $vehicleOrder->expected_begin_date && $this->newOrderEndDate >= $vehicleOrder->expected_end_date)
                ) {
                    $fail("O veículo já tem um pedido que coincide com estas datas.");
                }
        } else {
            $fail("Veículo encontra-se indisponível neste momento (estado: " . $vehicle->status . ")");
        }
    }
}
