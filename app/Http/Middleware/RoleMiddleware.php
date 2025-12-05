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
            return redirect('/home');
        }

        return $next($request);
    }

}
