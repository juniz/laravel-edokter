<?php

namespace App\Infrastructure\Provisioning;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Models\Domain\Provisioning\Server;
use Illuminate\Contracts\Container\Container;

class AdapterResolver
{
    public function __construct(
        private Container $container
    ) {}

    /**
     * Resolve adapter berdasarkan server type
     */
    public function resolve(Server $server): ProvisioningAdapterInterface
    {
        $adapterKey = 'provisioning.adapter.'.$server->type;

        if ($this->container->bound($adapterKey)) {
            return $this->container->make($adapterKey);
        }

        // Fallback ke default adapter
        return $this->container->make(ProvisioningAdapterInterface::class);
    }

    /**
     * Resolve adapter berdasarkan server type string
     */
    public function resolveByType(string $serverType): ProvisioningAdapterInterface
    {
        $adapterKey = 'provisioning.adapter.'.$serverType;

        if ($this->container->bound($adapterKey)) {
            return $this->container->make($adapterKey);
        }

        // Fallback ke default adapter
        return $this->container->make(ProvisioningAdapterInterface::class);
    }
}
