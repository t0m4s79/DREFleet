<?php

namespace App\Jobs;

use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ResetVehicleMonthlyRefuelRequests implements ShouldQueue
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
        if (Carbon::now()->isSameDay(Carbon::now()->startOfMonth())) {
            Vehicle::query()->update(['current_month_fuel_requests' => 0]);
        }
    }
}
