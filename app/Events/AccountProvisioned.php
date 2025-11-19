<?php

namespace App\Events;

use App\Models\Domain\Provisioning\PanelAccount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountProvisioned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PanelAccount $panelAccount
    ) {}
}
