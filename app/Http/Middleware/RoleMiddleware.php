<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $userRole = strtolower(Auth::user()->role);
        $allowedRoles = array_map('strtolower', $roles);

        // Check if user has any of the allowed roles
        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // Special case: allow Yasmin to access administrator routes
        if (Auth::user()->name === 'Yasmin' && in_array('administrator', $allowedRoles)) {
            return $next($request);
        }

        return redirect('/home')->with('error', 'Akses ditolak.');
    }

}
