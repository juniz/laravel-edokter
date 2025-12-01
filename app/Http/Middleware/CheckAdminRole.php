<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Allow customer/user untuk akses route check-availability dan get-details
        // karena ini adalah operasi yang customer butuhkan
        $currentPath = $request->path();
        $allowedRoutes = [
            'check-availability',
            'get-details',
        ];

        foreach ($allowedRoutes as $allowedRoute) {
            if (str_contains($currentPath, $allowedRoute)) {
                // Allow customer/user untuk akses route ini
                if ($user->hasRole('customer') || $user->hasRole('user') || $user->hasRole('admin')) {
                    return $next($request);
                }
            }
        }

        // Untuk route lainnya, hanya admin yang bisa akses
        if (! $user->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
