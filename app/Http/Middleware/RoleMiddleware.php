<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        // Check if user role is in allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Redirect based on role if not authorized
        return match ($userRole) {
            'staff' => redirect()->route('dashboard'),
            'manager' => redirect()->route('dashboard'),
            'hr' => redirect()->route('users.index'),
            'director' => redirect()->route('users.index'),
            'admin' => redirect()->route('users.index'),
            default => redirect()->route('dashboard'),
        };
    }
}