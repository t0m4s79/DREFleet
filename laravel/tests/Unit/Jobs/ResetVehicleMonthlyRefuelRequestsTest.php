<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use App\Jobs\ResetVehicleMonthlyRefuelRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetVehicleMonthlyRefuelRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_resets_monthly_request_number_on_first_day_of_month()
    {
        Vehicle::factory()->count(5)->create(['current_month_fuel_requests' => 5]);

        Carbon::setTestNow(Carbon::now()->startOfMonth()->addHour());

        (new ResetVehicleMonthlyRefuelRequests())->handle();

        $this->assertDatabaseHas('vehicles', ['current_month_fuel_requests' => 0]);
    }

    public function test_does_not_reset_monthly_request_number_on_non_first_day_of_month()
    {
        Vehicle::factory()->count(5)->create(['current_month_fuel_requests' => 5]);

        Carbon::setTestNow(Carbon::now()->startOfMonth()->addDay());

        (new ResetVehicleMonthlyRefuelRequests())->handle();

        $this->assertDatabaseHas('vehicles', ['current_month_fuel_requests' => 5]);
    }
}
