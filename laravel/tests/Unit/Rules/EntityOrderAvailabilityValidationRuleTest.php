<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use App\Rules\EntityOrderAvailabilityValidation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntityOrderAvailabilityValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $technician;
    protected $driver;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a technician and a driver to use in the tests
        $this->technician = User::factory()->create(['user_type' => 'Técnico', 'status' => 'Disponível']);
        $this->vehicle = Vehicle::factory()->create(['status' => 'Disponível']);

        $this->driver = Driver::factory()->create();
        User::find($this->driver->user_id)->update(['status' => 'Disponível']);
    }

    public function test_allows_new_order_for_technician_when_no_overlapping_orders_exist()
    {
        $validation = Validator::make(
            [
                'technician_id' => $this->technician->id,
                'expected_begin_date' => now()->addDays(3), // 3 days in the future
                'expected_end_date' => now()->addDays(5), // 5 days in the future
            ],
            [
                'technician_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertFalse($validation->fails());
    }

    public function test_allows_new_order_for_driver_when_no_overlapping_orders_exist()
    {
        $validation = Validator::make(
            [
                'driver_id' => $this->driver->user_id,
                'expected_begin_date' => now()->addDays(3), // 3 days in the future
                'expected_end_date' => now()->addDays(5), // 5 days in the future
            ],
            [
                'driver_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertFalse($validation->fails());
    }

    public function test_allows_new_order_for_vehicle_when_no_overlapping_orders_exist()
    {
        $validation = Validator::make(
            [
                'vehicle_id' => $this->vehicle->id,
                'expected_begin_date' => now()->addDays(3), // 3 days in the future
                'expected_end_date' => now()->addDays(5), // 5 days in the future
            ],
            [
                'vehicle_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertFalse($validation->fails());
    }

    public function test_fails_validation_for_technician_if_new_order_overlaps_existing_order()
    {
        // Create an existing order for the technician
        Order::factory()->create([
            'technician_id' => $this->technician->id,
            'expected_begin_date' => now()->addDays(2), // Existing order starts 2 days in the future
            'expected_end_date' => now()->addDays(4), // Existing order ends 4 days in the future
        ]);

        $validation = Validator::make(
            [
                'technician_id' => $this->technician->id,
                'expected_begin_date' => now()->addDays(3), // New order starts in the middle of the existing order
                'expected_end_date' => now()->addDays(6), // New order ends after the existing order
            ],
            [
                'technician_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(6) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'O técnico já tem uma reserva que sobrepõe o intervalo de datas especificado.',
            $validation->errors()->first('technician_id')
        );
    }

    public function test_fails_validation_for_driver_if_new_order_overlaps_existing_order()
    {
        // Create an existing order for the driver
        Order::factory()->create([
            'driver_id' => $this->driver->user_id,
            'expected_begin_date' => now()->addDays(2), // Existing order starts 2 days in the future
            'expected_end_date' => now()->addDays(4), // Existing order ends 4 days in the future
        ]);

        $validation = Validator::make(
            [
                'driver_id' => $this->driver->user_id,
                'expected_begin_date' => now()->addDays(1), // New order starts before existing order
                'expected_end_date' => now()->addDays(3), // New order ends in the middle of the existing order
            ],
            [
                'driver_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(6) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'O condutor já tem uma reserva que sobrepõe o intervalo de datas especificado.',
            $validation->errors()->first('driver_id')
        );
    }

    public function test_fails_validation_for_vehicle_if_new_order_overlaps_existing_order()
    {
        // Create an existing order for the driver
        Order::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'expected_begin_date' => now()->addDays(2), // Existing order starts 2 days in the future
            'expected_end_date' => now()->addDays(4), // Existing order ends 4 days in the future
        ]);

        $validation = Validator::make(
            [
                'vehicle_id' => $this->vehicle->id,
                'expected_begin_date' => now()->addDays(1), // New order starts before existing order
                'expected_end_date' => now()->addDays(5), // New order ends after existing order
            ],
            [
                'vehicle_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(6) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'O veículo já tem uma reserva que sobrepõe o intervalo de datas especificado.',
            $validation->errors()->first('vehicle_id')
        );
    }

    public function test_fails_validation_if_technician_not_found()
    {
        $validation = Validator::make(
            [
                'technician_id' => -1, // Non-existing technician ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'technician_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Utilizador não encontrado.',
            $validation->errors()->first('technician_id')
        );
    }

    public function test_fails_validation_if_driver_not_found()
    {
        $validation = Validator::make(
            [
                'driver_id' => -1, // Non-existing driver ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'driver_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Utilizador não encontrado.',
            $validation->errors()->first('driver_id')
        );
    }

    public function test_fails_validation_if_vehicle_not_found()
    {
        $validation = Validator::make(
            [
                'vehicle_id' => -1, // Non-existing technician ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'vehicle_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Veículo não encontrado.',
            $validation->errors()->first('vehicle_id')
        );
    }

    public function test_fails_validation_if_technician_has_unavailable_status()
    {
        $this->technician->update(['status' => Arr::random(['Escondido', 'Indisponível'])]);
        $validation = Validator::make(
            [
                'technician_id' => $this->technician->id, // Non-existing technician ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'technician_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Técnico não disponível: estado = ' . $this->technician->status,
            $validation->errors()->first('technician_id')
        );
    }

    public function test_fails_validation_if_driver_has_unavailable_status()
    {
        $user = User::find($this->driver->user_id);
        $user->update(['status' => Arr::random(['Escondido', 'Indisponível'])]);

        $validation = Validator::make(
            [
                'driver_id' => $this->driver->user_id, // Non-existing driver ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'driver_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Condutor não disponível: estado = ' . $user->status,
            $validation->errors()->first('driver_id')
        );
    }

    public function test_fails_validation_if_vehicle_has_unavailable_status()
    {
        $this->vehicle->update(['status' => Arr::random(['Escondido', 'Em manutenção', 'Indisponível'])]);
        $validation = Validator::make(
            [
                'vehicle_id' => $this->vehicle->id, // Non-existing technician ID
                'expected_begin_date' => now()->addDays(3),
                'expected_end_date' => now()->addDays(5),
            ],
            [
                'vehicle_id' => [
                    new EntityOrderAvailabilityValidation(
                        now()->addDays(3), // New order start date
                        now()->addDays(5) // New order end date
                    ),
                ],
            ]
        );

        $this->assertTrue($validation->fails());
        $this->assertEquals(
            'Veículo não disponível: estado = ' . $this->vehicle->status,
            $validation->errors()->first('vehicle_id')
        );
    }
}
