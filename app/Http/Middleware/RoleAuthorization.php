<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleAuthorization
{
    /**
     * Handle an incoming request for role-based menu access control (FE-08).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Ensure user is logged in
        if (!$request->session()->has('username')) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $username = (string) $request->session()->get('username');
        $userRole = (string) $request->session()->get('role', 'dokter');
        $kdSps = (string) $request->session()->get('kd_sps', '');

        // 2. Superadmin or 'admin' username always has full access
        if ($userRole === 'admin' || strtolower($username) === 'admin') {
            return $next($request);
        }

        // 3. Match against allowed roles or specialization codes
        foreach ($roles as $role) {
            $roleLower = strtolower($role);
            if ($userRole === $roleLower || strtolower($username) === $roleLower) {
                return $next($request);
            }
            if (!empty($kdSps) && (strtolower($kdSps) === $roleLower || $kdSps === $role)) {
                return $next($request);
            }
        }

        // 4. Deny access if role does not match
        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses menu ini.');
    }
}
