<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\DocumentExpiryNotification;

class SendDocumentExpiryNotification implements ShouldQueue
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

        // Fetch vehicles with documents that expire within one month
        $vehicles = Vehicle::with('vehicleDocuments')
            ->whereHas('vehicleDocuments', function ($query) use ($currentDate, $oneMonthFromNow) {
                $query->where('expiration_date', '>=', $currentDate)
                    ->where('expiration_date', '<=', $oneMonthFromNow);
            })
            ->get();

        foreach ($vehicles as $vehicle) {
            $users = User::where('user_type', 'Gestor')
                ->orWhere('user_type', 'Administrador')
                ->get();  // Retrieve the collection of users

            foreach ($users as $user) {
                foreach ($vehicle->vehicleDocuments as $document) {
                    if ($document->expiration_date >= $currentDate && $document->expiration_date <= $oneMonthFromNow) {
                        $user->notify(new DocumentExpiryNotification($vehicle, $document));  // Notify each user
                    }
                }
            }
        }
    }
}
