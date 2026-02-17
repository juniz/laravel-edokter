<?php

namespace App\Http\Middleware;

use App\Models\Domain\Shared\Setting;
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
            'paymentGateway' => fn () => (function () {
                $setting = Setting::where('key', 'payment_gateway_settings')->first();
                $value = $setting?->value ?? [];

                $defaultGateway = $value['default_gateway'] ?? config('payment.default', 'manual');
                $midtransEnabled = $value['midtrans_enabled'] ?? true;
                $midtransIsProduction = $value['midtrans_is_production'] ?? (bool) config('payment.midtrans.is_production', false);

                if ($defaultGateway === 'midtrans' && $midtransEnabled === false) {
                    $defaultGateway = 'manual';
                }

                return [
                    'default_gateway' => $defaultGateway,
                    'midtrans_enabled' => (bool) $midtransEnabled,
                    'midtrans_is_production' => (bool) $midtransIsProduction,
                    'manual_only' => $defaultGateway === 'manual',
                ];
            })(),
            'siteFooter' => fn () => (
                Setting::where('key', 'site_footer')->first()?->value
                ?? [
                    'description' => 'Solusi produk & layanan terbaik untuk bisnis Anda. Performa tinggi, keamanan terjamin, dan dukungan 24/7.',
                    'quick_links_title' => 'Produk',
                    'quick_links' => [
                        ['label' => 'Shared Hosting', 'href' => route('catalog.guest', absolute: false)],
                        ['label' => 'VPS', 'href' => route('catalog.guest', absolute: false)],
                        ['label' => 'Domain', 'href' => route('catalog.guest', absolute: false)],
                    ],
                    'support_links_title' => 'Dukungan',
                    'support_links' => [
                        ['label' => 'Client Area', 'href' => route('login', absolute: false)],
                        ['label' => 'Knowledge Base', 'href' => '#'],
                        ['label' => 'Hubungi Kami', 'href' => '#'],
                    ],
                ]
            ),
            'cartCount' => $cartCount,
        ]);
    }
}
