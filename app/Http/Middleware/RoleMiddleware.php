<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Access denied. Insufficient permissions.',
                ],
                403,
            );
        }

        return $next($request);
    }
}
