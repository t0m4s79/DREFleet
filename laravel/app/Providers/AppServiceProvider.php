<?php

namespace App\Providers;

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
    }
}
