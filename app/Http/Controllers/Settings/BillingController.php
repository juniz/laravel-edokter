<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    /**
     * Show billing settings form
     */
    public function edit(): Response
    {
        // Get from database settings first, fallback to config
        $setting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();

        $pphRate = $setting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);
        $annualDiscountRate = $setting?->value['annual_discount_rate'] ?? config('billing.annual_discount_rate', 0.20);

        return Inertia::render('settings/billing', [
            'settings' => [
                'pph_rate' => $pphRate,
                'annual_discount_rate' => $annualDiscountRate,
            ],
        ]);
    }

    /**
     * Update billing settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'pph_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'annual_discount_rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        // Update config file atau database
        // Untuk sekarang, kita simpan ke .env atau database
        // Karena config file tidak bisa diubah runtime, kita bisa simpan ke database atau .env

        // Set environment variable (akan memerlukan package seperti dotenv editor)
        // Atau simpan ke database settings table

        // Untuk sementara, kita akan simpan ke database settings
        $setting = \App\Models\Domain\Shared\Setting::firstOrCreate(
            ['key' => 'billing_settings'],
            ['value' => []]
        );

        $setting->update([
            'value' => [
                'pph_rate' => $validated['pph_rate'],
                'annual_discount_rate' => $validated['annual_discount_rate'],
            ],
        ]);

        return redirect()->route('settings.billing.edit')
            ->with('success', 'Pengaturan billing berhasil diperbarui.');
    }
}
