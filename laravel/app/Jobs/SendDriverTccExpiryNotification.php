<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Driver;
use App\Notifications\DriverTccExpiryNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDriverTccExpiryNotification implements ShouldQueue
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
        // Get the current date and the date one month from now
        $currentDate = now();
        $oneMonthFromNow = now()->addMonth();

        // Fetch drivers with tcc that expires within one month
        $drivers = Driver::where('tcc_expiration_date', '>=', $currentDate)
                    ->where('tcc_expiration_date', '<=', $oneMonthFromNow)
                    ->get();

        foreach ($drivers as $driver) {
            $users = User::where('user_type', 'Gestor')
                ->orWhere('user_type', 'Administrador')
                ->get();  // Retrieve the collection of users

            foreach ($users as $user) {
                if ($driver->tcc_expiration_date >= $currentDate && $driver->tcc_expiration_date <= $oneMonthFromNow) {
                    $user->notify(new DriverTccExpiryNotification($driver));                            // Notify each manager
                }
            }

            User::find($driver->user_id)->notify(new DriverTccExpiryNotification($driver));     // Notify the driver
        }
    }
}
