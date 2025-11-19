<?php

namespace App\Domain\Provisioning\Contracts;

use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;

interface ProvisioningAdapterInterface
{
    public function createAccount(Subscription $sub, array $params): PanelAccount;
    public function suspendAccount(PanelAccount $acc): void;
    public function unsuspendAccount(PanelAccount $acc): void;
    public function terminateAccount(PanelAccount $acc): void;
    public function changePlan(PanelAccount $acc, string $planCode): void;
}

