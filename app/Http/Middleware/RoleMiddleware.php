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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = Auth::user();

        // Check if user is superadmin (can access everything)
        if ($user->roles->contains('name', 'superadmin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->roles->contains('name', $role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki permission untuk mengakses resource ini.',
                'required_roles' => $roles,
                'user_roles' => $user->roles->pluck('name')->toArray()
            ], 403);
        }

        return $next($request);
    }
}
