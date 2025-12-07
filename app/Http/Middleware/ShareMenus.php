<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareMenus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        Inertia::share('menus', function () use ($user) {
            if (! $user) {
                return [];
            }

            // Ambil semua menu secara flat
            $allMenus = Menu::orderBy('order')->get();

            // Index berdasarkan ID
            $indexed = $allMenus->keyBy('id');

            // Cek apakah user adalah customer (bukan admin)
            $isCustomer = $user->hasRole('customer') || $user->hasRole('user');

            // Cari Customer Area menu
            $customerAreaMenu = $allMenus->firstWhere('title', 'Customer Area');

            // Recursive builder (filtered by permission)
            $buildTree = function ($parentId = null) use (&$buildTree, $indexed, $user, $isCustomer, $customerAreaMenu) {
                return $indexed
                    ->filter(
                        fn ($menu) => $menu->parent_id === $parentId &&
                            (! $menu->permission_name || $user->hasPermissionTo($menu->permission_name))
                    )
                    ->map(function ($menu) use (&$buildTree, $isCustomer, $customerAreaMenu) {
                        // Jika user adalah customer dan menu ini adalah Customer Area, skip parent
                        if ($isCustomer && $customerAreaMenu && $menu->id === $customerAreaMenu->id) {
                            return null;
                        }

                        $menu->children = $buildTree($menu->id)->values();

                        return $menu;
                    })
                    ->filter(
                        fn ($menu) => $menu !== null && ($menu->route || $menu->children->isNotEmpty())
                    )
                    ->values();
            };

            $menus = $buildTree();

            // Jika user adalah customer, flatten Customer Area children ke root level
            if ($isCustomer && $customerAreaMenu) {
                // Ambil children dari Customer Area
                $customerAreaChildren = $buildTree($customerAreaMenu->id);

                // Filter out Customer Area parent dari menus
                $menus = $menus->reject(function ($menu) use ($customerAreaMenu) {
                    return $menu->id === $customerAreaMenu->id;
                });

                // Gabungkan children Customer Area ke root menus dan sort by order
                $menus = $menus->concat($customerAreaChildren)->sortBy('order')->values();
            }

            return $menus;
        });

        return $next($request);
    }
}
