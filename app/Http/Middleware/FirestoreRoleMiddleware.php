<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirestoreRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $group) {
            $groupRoles = preg_split('/\|/', (string) $group) ?: [];
            foreach ($groupRoles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'No tienes permisos para acceder a este recurso.');
    }
}
