<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MarginUpdateRequest;
use App\Models\Domain\Shared\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarginController extends Controller
{
    /**
     * Show the margin settings page.
     */
    public function edit(Request $request): Response
    {
        $marginSettings = Setting::where('key', 'profit_margin')->first();

        $defaultSettings = [
            'domain_margin_type' => 'percentage',
            'domain_margin_value' => 0,
            'ssh_margin_type' => 'percentage',
            'ssh_margin_value' => 0,
        ];

        $settings = $marginSettings?->value ?? $defaultSettings;

        return Inertia::render('settings/margin', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the margin settings.
     */
    public function update(MarginUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Setting::updateOrCreate(
            ['key' => 'profit_margin'],
            [
                'value' => [
                    'domain_margin_type' => $validated['domain_margin_type'],
                    'domain_margin_value' => (float) $validated['domain_margin_value'],
                    'ssh_margin_type' => $validated['ssh_margin_type'],
                    'ssh_margin_value' => (float) $validated['ssh_margin_value'],
                ],
            ]
        );

        return redirect()->back()->with('success', 'Pengaturan margin keuntungan berhasil disimpan.');
    }
}
