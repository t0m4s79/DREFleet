<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;

class EntityOrderAvailabilityValidation implements ValidationRule
{
    protected $expected_begin_date;
    protected $expected_end_date;
    protected $orderId;

    public function __construct($expected_begin_date, $expected_end_date, $orderId = null)
    {
        $this->expected_begin_date = $expected_begin_date;
        $this->expected_end_date = $expected_end_date;
        $this->orderId = $orderId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        switch ($attribute) {
            case 'technician_id':
                $this->technician($attribute, $value, $fail);
                break;
                
            case 'driver_id':
                $this->driver($attribute, $value, $fail);
                break;

            case 'vehicle_id':
                $this->vehicle($attribute, $value, $fail);
                break;
            
            default:
                break;
            }
    }

    private function technician(string $attribute, mixed $value, Closure $fail)
    {
        $user = User::find($value);

        // Check if the vehicle exists
        if (!$user) {
            $fail('Utilizador não encontrado.');
            return;
        }

        if ($user->status == 'Indisponível' || $user->status == 'Escondido') {
            $fail('Técnico não disponível: estado = ' . $user->status);
        }

        // Get all future orders for the vehicle that overlap
        $overlappingOrders = $user->technicianOrders()
            ->where('expected_begin_date', '>', now()) // Only consider future orders
            ->where('id', '!=', $this->orderId)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // New order starts during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_end_date) // New end date after existing start date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // New start date before existing end date
                })
                ->orWhere(function ($q) {
                    // New order ends during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_begin_date) // New start date before existing end date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // Existing start date before new start date
                })
                ->orWhere(function ($q) {
                    // New order completely overlaps an existing order
                    $q->where('expected_begin_date', '<=', $this->expected_begin_date) // Existing starts before or at new start date
                      ->where('expected_end_date', '>=', $this->expected_end_date); // Existing ends after or at new end date
                });
            })
            ->when($this->orderId, function ($query) {
                // Exclude the current order from the query if editing
                $query->where('id', '!=', $this->orderId);
            })
            ->exists(); // Check if any overlapping orders exist

            if ($overlappingOrders) {
            $fail('O técnico já tem uma reserva que sobrepõe o intervalo de datas especificado.');
        }
    }

    private function driver(string $attribute, mixed $value, Closure $fail)
    {
        $driver = Driver::find($value);

        // Check if the vehicle exists
        if (!$driver) {
            $fail('Utilizador não encontrado.');
            return;
        }

        if ($driver->status == 'Indisponível' || $driver->status == 'Escondido') {
            $fail('Condutor não disponível: estado = ' . $driver->status);
        }

        // Get all future orders for the vehicle that overlap
        $overlappingOrders = $driver->orders()
            ->where('expected_begin_date', '>', now()) // Only consider future orders
            ->where(function ($query) {
                $query->where(function ($q) {
                    // New order starts during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_end_date) // New end date after existing start date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // New start date before existing end date
                })
                ->orWhere(function ($q) {
                    // New order ends during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_begin_date) // New start date before existing end date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // Existing start date before new start date
                })
                ->orWhere(function ($q) {
                    // New order completely overlaps an existing order
                    $q->where('expected_begin_date', '<=', $this->expected_begin_date) // Existing starts before or at new start date
                      ->where('expected_end_date', '>=', $this->expected_end_date); // Existing ends after or at new end date
                });
            })
            ->when($this->orderId, function ($query) {
                // Exclude the current order from the query if editing
                $query->where('id', '!=', $this->orderId);
            })
            ->exists(); // Check if any overlapping orders exist

        if ($overlappingOrders) {
            $fail('O condutor já tem uma reserva que sobrepõe o intervalo de datas especificado.');
        }
    }

    private function vehicle(string $attribute, mixed $value, Closure $fail)
    {
        $vehicle = Vehicle::find($value);

        // Check if the vehicle exists
        if (!$vehicle) {
            $fail('Veículo não encontrado.');
            return;
        }

        if ($vehicle->status == 'Indisponível' || $vehicle->status == 'Escondido' || $vehicle->status == 'Em manutenção') {
            $fail('Veículo não disponível: estado = ' . $vehicle->status);
        }

        // Get all future orders for the vehicle that overlap
        $overlappingOrders = $vehicle->orders()
            ->where('expected_begin_date', '>', now()) // Only consider future orders
            ->where(function ($query) {
                $query->where(function ($q) {
                    // New order starts during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_end_date) // New end date after existing start date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // New start date before existing end date
                })
                ->orWhere(function ($q) {
                    // New order ends during an existing order
                    $q->where('expected_begin_date', '<', $this->expected_begin_date) // New start date before existing end date
                      ->where('expected_end_date', '>', $this->expected_begin_date); // Existing start date before new start date
                })
                ->orWhere(function ($q) {
                    // New order completely overlaps an existing order
                    $q->where('expected_begin_date', '<=', $this->expected_begin_date) // Existing starts before or at new start date
                      ->where('expected_end_date', '>=', $this->expected_end_date); // Existing ends after or at new end date
                });
            })
            ->when($this->orderId, function ($query) {
                // Exclude the current order from the query if editing
                $query->where('id', '!=', $this->orderId);
            })
            ->exists(); // Check if any overlapping orders exist

        if ($overlappingOrders) {
            $fail('O veículo já tem uma reserva que sobrepõe o intervalo de datas especificado.');
        }
    }
}