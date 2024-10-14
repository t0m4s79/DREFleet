<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\OrderRequiresApprovalNotification;

class SendOrderRequiresApprovalNotification implements ShouldQueue
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

        // Fetch orders that begin within one month and are not approved
        $orders = Order::where('expected_begin_date', '>=', $currentDate)
                    ->where('expected_begin_date', '<=', $oneMonthFromNow)
                    ->where('approved_date', null)
                    ->where('manager_id', null)
                    ->get();

        foreach ($orders as $order) {
            $users = User::where('user_type', 'Gestor')
                ->orWhere('user_type', 'Administrador')
                ->get();  // Retrieve the collection of users

            foreach ($users as $user) {
                if ($order->expected_begin_date >= $currentDate && $order->expected_begin_date <= $oneMonthFromNow) {
                    $user->notify(new OrderRequiresApprovalNotification($order));  // Notify each user
                }
            }
        }
    }

}
