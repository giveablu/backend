<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'response' => false,
                'message' => ['Unauthenticated.'],
            ], 401);
        }

        $userRole = is_string($user->role) ? trim($user->role) : '';
        $requiredRole = is_string($role) ? trim($role) : '';

        if (strcasecmp($userRole, $requiredRole) === 0) {
            return $next($request);
        }

        return response()->json([
            'response' => false,
            'message' => ["This endpoint is restricted to {$requiredRole}s."],
        ], 403);
    }
}
