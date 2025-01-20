<?php

namespace App\Providers;

use App\Models\OrderOccurrence;
use App\Models\User;
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
            return $user->isAdmin() || $user->isManager() || $user->isDriver()
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
    }
}
