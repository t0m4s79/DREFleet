<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AppendUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        $permissions = $user ? [
            'isAdmin' => $user->user_type === 'Administrador',
            'isManager' => $user->user_type === 'Gestor',
            'isTechnician' => $user->user_type === 'Técnico',
            'isDriver' => $user->user_type === 'Condutor',
            'isNone' => $user->user_type === 'Nenhum',
        ] : [];

        Inertia::share('permissions', $permissions);

        return $next($request);
    }
}
