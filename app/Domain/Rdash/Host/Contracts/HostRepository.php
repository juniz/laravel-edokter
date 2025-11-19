<?php

namespace App\Domain\Rdash\Host\Contracts;

use App\Domain\Rdash\Host\ValueObjects\Host;

interface HostRepository
{
    /**
     * Get all child nameservers by domain id
     *
     * @param array<string, mixed> $filters
     * @return array<int, Host>
     */
    public function getByDomainId(int $domainId, array $filters = []): array;

    /**
     * Get host by domain id and host id
     */
    public function getById(int $domainId, int $hostId): ?Host;

    /**
     * Create child nameserver
     *
     * @param array<string, mixed> $data
     */
    public function create(int $domainId, array $data): Host;

    /**
     * Update child nameserver
     *
     * @param array<string, mixed> $data
     */
    public function update(int $domainId, int $hostId, array $data): Host;

    /**
     * Delete child nameserver
     */
    public function delete(int $domainId, int $hostId): bool;
}

