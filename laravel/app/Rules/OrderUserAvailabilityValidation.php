<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderUserAvailabilityValidation implements ValidationRule
{
    protected $newOrderStartDate;
    protected $newOrderEndDate;
    protected $currentOrderId;
    
    public function __construct($newOrderStartDate, $newOrderEndDate, $currentOrderId = null)
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
        $user = User::find($value);

        $userOrders = Order::where('driver_id', $user->id)->orWhere('technician_id', $user->id)
            ->when($this->currentOrderId, function ($query) {
                $query->where('id', '!=', $this->currentOrderId);
            })
            ->get();

        if ($user->status != 'Escondido' || $user->status != 'Indisponível') {
            // Check if the new order overlaps with any existing order
            foreach($userOrders as $userOrder)
                if (
                    // New order starts during an existing order
                    ($this->newOrderStartDate >= $userOrder->expected_begin_date && $this->newOrderStartDate <= $userOrder->expected_end_date) ||
                    // New order ends during an existing order
                    ($this->newOrderEndDate >= $userOrder->expected_begin_date && $this->newOrderEndDate <= $userOrder->expected_end_date) ||
                    // New order fully covers an existing order
                    ($this->newOrderStartDate <= $userOrder->expected_begin_date && $this->newOrderEndDate >= $userOrder->expected_end_date)
                ) {
                    $fail("O utilizador já tem um pedido que coincide com estas datas.");
                }
        } else {
            $fail("Utilizador encontra-se indisponível neste momento (estado: " . $user->status . ")");
        }
    }
}
