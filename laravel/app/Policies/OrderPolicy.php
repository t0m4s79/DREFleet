<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //  
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    // public function view(User $user, Order $order): bool
    // {
    //     //
    //     return (auth()->$user->isAdmin() || auth()->$user->isManager() || (auth()->$user->check() && $order->$user->id == auth()->id));
    // }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
        return $user->user_type == 'Administrador' || $user->user_type == 'Gestor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        //
        return $user->user_type == 'Administrador' || $user->user_type == 'Gestor';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        //
        return $user->user_type == 'Administrador' || $user->user_type == 'Gestor';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        //
        return $user->user_type == 'Administrador';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        //
        return $user->user_type == 'Administrador';
    }
}
