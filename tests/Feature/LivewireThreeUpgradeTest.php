<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LivewireThreeUpgradeTest extends TestCase
{
    public function test_livewire_config_uses_the_livewire_three_shape(): void
    {
        $config = require config_path('livewire.php');

        foreach ([
            'asset_url',
            'app_url',
            'middleware_group',
            'manifest_path',
            'back_button_cache',
        ] as $removedKey) {
            $this->assertArrayNotHasKey(
                $removedKey,
                $config,
                "config/livewire.php still contains removed Livewire 2 key [{$removedKey}]."
            );
        }

        foreach ([
            'class_namespace',
            'view_path',
            'layout',
            'lazy_placeholder',
            'temporary_file_upload',
            'render_on_redirect',
            'legacy_model_binding',
            'inject_assets',
            'navigate',
            'inject_morph_markers',
            'smart_wire_keys',
            'pagination_theme',
            'release_token',
        ] as $requiredKey) {
            $this->assertArrayHasKey(
                $requiredKey,
                $config,
                "config/livewire.php is missing Livewire 3 key [{$requiredKey}]."
            );
        }
    }

    public function test_livewire_codebase_contains_no_deprecated_livewire_two_event_apis(): void
    {
        $deprecatedPatterns = [
            'dispatchBrowserEvent(' => '/dispatchBrowserEvent\(/',
            '$this->emit(' => '/\$this->emit(?:Self|To)?\(/',
            '$emit(' => '/\$emit(?:To)?\(/',
            'Livewire.emit(' => '/Livewire\.emit\(/',
            'window.livewire.emit(' => '/window\.livewire\.emit\(/',
            'window.livewire.on(' => '/window\.livewire\.on\(/',
            'livewire:load' => '/livewire:load/',
            'livewire:update' => '/livewire:update/',
            'livewire:dom-updated' => '/livewire:dom-updated/',
            "Livewire.hook('message.processed'" => '/Livewire\.hook\([\'"]message\.processed[\'"]/',
            "Livewire.hook('element.updated'" => '/Livewire\.hook\([\'"]element\.updated[\'"]/',
        ];

        $paths = [
            app_path('Http/Livewire'),
            resource_path('views'),
        ];

        $matches = [];

        foreach ($paths as $path) {
            foreach (File::allFiles($path) as $file) {
                $contents = $file->getContents();
                $relativePath = str_replace(base_path() . '/', '', $file->getPathname());

                foreach ($deprecatedPatterns as $label => $pattern) {
                    if (preg_match($pattern, $contents)) {
                        $matches[] = "{$relativePath} -> {$label}";
                    }
                }
            }
        }

        $this->assertSame([], $matches, "Deprecated Livewire 2 APIs remain:\n" . implode("\n", $matches));
    }
}
