<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Menu;

class CheckMenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Abaikan jika belum login
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil route yang sedang diakses
        $currentPath = $request->path(); // e.g., "customer/tickets" or "admin/products"
        $currentPathWithSlash = '/' . ltrim($currentPath, '/'); // e.g., "/customer/tickets"

        // Cari menu berdasarkan route (exact match)
        $menu = Menu::where('route', $currentPathWithSlash)
            ->orWhere('route', $currentPath)
            ->first();

        // Jika tidak ditemukan exact match, coba cari dengan matching base path
        // Contoh: /admin/products/create akan match dengan /admin/products
        if (!$menu && str_contains($currentPath, '/')) {
            $pathParts = explode('/', $currentPath);
            if (count($pathParts) >= 2) {
                $basePath = '/' . $pathParts[0] . '/' . $pathParts[1];
                $menu = Menu::where('route', $basePath)->first();
            }

            // Jika masih tidak ditemukan, coba untuk route dengan 3 parts (admin/products/create)
            if (!$menu && count($pathParts) >= 3) {
                $basePath = '/' . $pathParts[0] . '/' . $pathParts[1];
                $menu = Menu::where('route', $basePath)->first();
            }
        }

        // Jika menu ditemukan dan punya permission
        if ($menu && $menu->permission_name) {
            try {
                // Gunakan hasPermissionTo dari Spatie Permission
                if (!$user->hasPermissionTo($menu->permission_name)) {
                    abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
                }
            } catch (\Exception $e) {
                // Jika permission tidak ada, log error tapi biarkan pass untuk development
                // In production, mungkin ingin abort atau log ke monitoring
                \Log::warning("Permission check failed: {$menu->permission_name}", [
                    'user_id' => $user->id,
                    'route' => $currentPath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Jika menu tidak ditemukan, biarkan pass (mungkin route public atau belum ada menu)
        return $next($request);
    }
}
