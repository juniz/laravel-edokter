<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Domain\Shared\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentGatewayController extends Controller
{
    public function edit(): Response
    {
        $setting = Setting::where('key', 'payment_gateway_settings')->first();

        $defaults = [
            'default_gateway' => config('payment.default', 'manual'),
            'midtrans_enabled' => true,
            'midtrans_is_production' => (bool) config('payment.midtrans.is_production', false),
            'midtrans_verify_webhook_signature' => (bool) config('payment.midtrans.verify_webhook_signature', true),
        ];

        $settings = array_merge($defaults, $setting?->value ?? []);

        if (($settings['midtrans_enabled'] ?? true) === false && ($settings['default_gateway'] ?? 'manual') === 'midtrans') {
            $settings['default_gateway'] = 'manual';
        }

        return Inertia::render('settings/payment-gateway', [
            'settings' => $settings,
            'env' => [
                'midtrans_has_server_key' => (bool) config('payment.midtrans.server_key'),
                'midtrans_has_client_key' => (bool) config('payment.midtrans.client_key'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_gateway' => ['required', 'in:manual,midtrans,xendit,tripay'],
            'midtrans_enabled' => ['required', 'boolean'],
            'midtrans_is_production' => ['required', 'boolean'],
            'midtrans_verify_webhook_signature' => ['required', 'boolean'],
        ]);

        $value = [
            'default_gateway' => $validated['default_gateway'],
            'midtrans_enabled' => (bool) $validated['midtrans_enabled'],
            'midtrans_is_production' => (bool) $validated['midtrans_is_production'],
            'midtrans_verify_webhook_signature' => (bool) $validated['midtrans_verify_webhook_signature'],
        ];

        if ($value['midtrans_enabled'] === false && $value['default_gateway'] === 'midtrans') {
            $value['default_gateway'] = 'manual';
        }

        Setting::updateOrCreate(
            ['key' => 'payment_gateway_settings'],
            ['value' => $value]
        );

        return redirect()->back()->with('success', 'Pengaturan payment gateway berhasil disimpan.');
    }
}
