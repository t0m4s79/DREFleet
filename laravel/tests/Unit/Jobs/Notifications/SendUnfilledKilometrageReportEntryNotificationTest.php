<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use Carbon\CarbonPeriod;
use App\Models\VehicleKilometrageReport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\SendUnfilledKilometrageReportEntryNotification;

class SendUnfilledKilometrageReportEntryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_for_unfilled_kilometrage_report_entries()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        $user = User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();

        // Delete existing kilometrage reports for the vehicle
        VehicleKilometrageReport::where('vehicle_id', $vehicle->id)->delete();

        // Dispatch the job (assumes this job checks for unfilled entries in the previous month)
        (new SendUnfilledKilometrageReportEntryNotification())->handle();

        // Assert that the user was notified for the missing kilometrage report
        Notification::assertSentTo(
            [$user],
            \App\Notifications\UnfilledKilometrageReportEntryNotification::class
        );
    }

    public function test_notification_not_sent_for_filled_kilometrage_report_entries()
    {
        // Fake the notifications
        Notification::fake();

        // Set up test data
        User::factory()->create(['user_type' => 'Gestor']);
        $vehicle = Vehicle::factory()->create();

        $previousMonthFirstDay = now()->subMonth()->startOfMonth();
        $previousMonthLastDay = now()->subMonth()->endOfMonth();

        // Create a report for every day of the previous month
        foreach (CarbonPeriod::create($previousMonthFirstDay, $previousMonthLastDay) as $date) {
            VehicleKilometrageReport::factory()->create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => Driver::factory(),
                'date' => $date,
            ]);
        }

        // Dispatch the job
        (new SendUnfilledKilometrageReportEntryNotification())->handle();

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }
}
