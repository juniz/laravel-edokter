<?php

namespace App\Http\Middleware;

use App\Models\SettingApp;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $cartCount = 0;

        if ($user && $user->customer) {
            $cart = \App\Models\Domain\Order\Cart::where('customer_id', $user->customer->id)->first();
            if ($cart) {
                $cartCount = $cart->items()->count();
            }
        }

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'setting' => fn () => SettingApp::first(),
            'cartCount' => $cartCount,
        ]);
    }
}
