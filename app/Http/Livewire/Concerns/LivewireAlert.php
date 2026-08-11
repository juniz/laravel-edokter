<?php

namespace App\Http\Livewire\Concerns;

use Jantinnerezo\LivewireAlert\Facades\LivewireAlert as LivewireAlertFacade;

trait LivewireAlert
{
    protected function alert(string $type, string $title, array $options = []): void
    {
        $alert = LivewireAlertFacade::title($title);

        if (method_exists($alert, $type)) {
            $alert->{$type}();
        } else {
            $options['icon'] = $options['icon'] ?? $type;
        }

        foreach ([
            'onConfirmed' => 'onConfirm',
            'onDenied' => 'onDeny',
            'onDismissed' => 'onDismiss',
        ] as $legacyEvent => $modernEvent) {
            if (! array_key_exists($legacyEvent, $options)) {
                continue;
            }

            $action = $options[$legacyEvent];
            unset($options[$legacyEvent]);

            if (is_string($action) && $action !== '') {
                $alert->{$modernEvent}($action);
            }
        }

        $alert->withOptions($options)->show();
    }
}
