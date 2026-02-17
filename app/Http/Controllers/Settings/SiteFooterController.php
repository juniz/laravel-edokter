<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Domain\Shared\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteFooterController extends Controller
{
    public function edit(): Response
    {
        $setting = Setting::where('key', 'site_footer')->first();

        $defaults = [
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
        ];

        return Inertia::render('settings/site-footer', [
            'settings' => array_merge($defaults, $setting?->value ?? []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
            'quick_links_title' => ['required', 'string', 'max:50'],
            'quick_links' => ['nullable', 'array'],
            'quick_links.*.label' => ['required', 'string', 'max:50'],
            'quick_links.*.href' => ['required', 'string', 'max:255'],
            'support_links_title' => ['required', 'string', 'max:50'],
            'support_links' => ['nullable', 'array'],
            'support_links.*.label' => ['required', 'string', 'max:50'],
            'support_links.*.href' => ['required', 'string', 'max:255'],
        ]);

        Setting::updateOrCreate(
            ['key' => 'site_footer'],
            [
                'value' => [
                    'description' => $validated['description'] ?? null,
                    'quick_links_title' => $validated['quick_links_title'],
                    'quick_links' => $validated['quick_links'] ?? [],
                    'support_links_title' => $validated['support_links_title'],
                    'support_links' => $validated['support_links'] ?? [],
                ],
            ]
        );

        return redirect()->back()->with('success', 'Pengaturan footer berhasil disimpan.');
    }
}

