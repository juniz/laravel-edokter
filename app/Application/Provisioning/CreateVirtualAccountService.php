<?php

namespace App\Application\Provisioning;

use App\Infrastructure\Provisioning\AdapterResolver;
use App\Infrastructure\Provisioning\Adapters\AaPanelAdapter;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;

class CreateVirtualAccountService
{
    public function __construct(
        private AdapterResolver $adapterResolver
    ) {}

    /**
     * Create virtual account/sub user di aaPanel
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): PanelAccount
    {
        $server = Server::findOrFail($data['server_id']);

        // Validasi server type harus aapanel
        if ($server->type !== 'aapanel') {
            throw new \Exception('Fitur ini hanya tersedia untuk server aaPanel');
        }

        // Resolve adapter berdasarkan server type
        $adapter = $this->adapterResolver->resolveByType($server->type);

        if (! $adapter instanceof AaPanelAdapter) {
            throw new \Exception('Adapter tidak didukung untuk operasi ini');
        }

        // Create virtual account menggunakan adapter
        return $adapter->createVirtualAccount($server, $data);
    }
}

