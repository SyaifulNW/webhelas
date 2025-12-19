<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
       public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (strtolower(Auth::user()->role) !== strtolower($role)) {
            // Special case: allow Yasmin to access administrator routes
            if (Auth::user()->name === 'Yasmin' && strtolower($role) === 'administrator') {
                return $next($request);
            }
            return redirect('/home');
        }

        return $next($request);
    }

}
