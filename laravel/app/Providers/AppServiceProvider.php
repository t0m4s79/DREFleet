<?php

namespace App\Providers;

use App\Models\OrderOccurrence;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/ErrorMessagesHelper.php');

        //Grants all abilities to Admin users
        Gate::before(function (User $user, string $ability) {
            if($user->isAdmin()) {
                return true;
            }
        });

        // Order Gates
        Gate::define('create-order', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-order', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-order', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('approve-order', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Order Occurrence Gates
        Gate::define('create-order-occurrence', function (User $user) {
            return $user->isAdmin() || $user->isManager() || $user->isDriver()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-order-occurrence', function (User $user, OrderOccurrence $occurence) {
            return $user->isAdmin() || $user->isManager() || ($user->isDriver() && $occurence->order()->driver() === $user->id())   //Check if driver is the one in the order 
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-order-occurrence', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Order Route Gates
        Gate::define('create-order-route', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-order-route', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-order-route', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        //
        Gate::define('create-order-stop', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-order-stop', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-order-stop', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Address Gates
        Gate::define('create-place', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-place', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-place', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Kid Gates
        // Re-used these gates for KidEmails and KidPhoneNumbers since same logic applies
        Gate::define('view-kid', function (User $user) {
            return $user->isAdmin() || $user->isManager() || $user->isTechnician()
            ? Response::allow()
            : Response::denyWithStatus(403);
        });

        Gate::define('create-kid', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-kid', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-kid', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // User Gates
        // Re-used gates for Techinicians since same logic applies
        Gate::define('view-user', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('create-user', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-user', function (User $user) {
            return $user->isAdmin() || $user->isManager() //TODO: should user be able to edit own data?
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-user', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Manager Gates
        Gate::define('view-manager', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('create-manager', function (User $user) {
            return $user->isAdmin()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-manager', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-manager', function (User $user) {
            return $user->isAdmin()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        // Driver Gates
        ////TODO: should Drivers use same logic as Users?
        Gate::define('view-driver', function (User $user) {
            return $user->isAdmin() || $user->isManager() || $user->isDriver()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('create-driver', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-driver', function (User $user) {
            return $user->isAdmin() || $user->isManager()  //TODO: should driver be able to edit own data?
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-driver', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        //Vehicle Gates
        Gate::define('create-vehicle', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('edit-vehicle', function (User $user, Vehicle $vehicle) {
            return $user->isAdmin() || $user->isManager()       //TODO: should Drivers be able to edit vehicle?
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        Gate::define('delete-vehicle', function (User $user) {
            return $user->isAdmin() || $user->isManager()
                ? Response::allow()
                : Response::denyWithStatus(403);
        });

        
    }
}
