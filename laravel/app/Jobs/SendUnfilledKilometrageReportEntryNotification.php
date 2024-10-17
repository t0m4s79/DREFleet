<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\UnfilledKilometrageReportEntryNotification;

class SendUnfilledKilometrageReportEntryNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the first and last days of the previous month
        $previousMonthFirstDay = now()->subMonth()->startOfMonth();
        $previousMonthLastDay = now()->subMonth()->endOfMonth();

        // Fetch vehicles and their kilometrage reports from the previous month
        $vehicles = Vehicle::with(['kilometrageReports' => function ($query) use ($previousMonthFirstDay, $previousMonthLastDay) {
            $query->where('date', '>=', $previousMonthFirstDay)
                ->where('date', '<=', $previousMonthLastDay);
        }])->get();

        foreach ($vehicles as $vehicle) {
            // Get all days in the previous month
            $expectedDates = [];
            for ($date = $previousMonthFirstDay->copy(); $date <= $previousMonthLastDay; $date->addDay()) {
                $expectedDates[] = $date->format('Y-m-d');  // Store the date in 'Y-m-d' format
            }

            // Get actual report dates for the vehicle
            $actualDates = $vehicle->kilometrageReports->pluck('date')->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            })->toArray();

            // Find missing dates
            $missingDates = array_diff($expectedDates, $actualDates);

            if (!empty($missingDates)) {
                // Notify users if there are any missing dates
                $users = User::whereIn('user_type', ['Gestor', 'Administrador'])->get();

                foreach ($users as $user) {
                    foreach ($missingDates as $missingDate) {
                        $user->notify(new UnfilledKilometrageReportEntryNotification($vehicle, $missingDate));
                    }
                }
            }
        }
    }
}
