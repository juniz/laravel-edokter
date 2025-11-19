<?php

namespace App\Domain\Rdash\Forwarding\Contracts;

use App\Domain\Rdash\Forwarding\ValueObjects\Forwarding;

interface ForwardingRepository
{
    /**
     * Get all domain forwarding by domain id
     *
     * @return array<int, Forwarding>
     */
    public function getByDomainId(int $domainId): array;

    /**
     * Create or update domain forwarding
     *
     * @param array<string, mixed> $data
     */
    public function createOrUpdate(int $domainId, array $data): Forwarding;

    /**
     * Delete domain forwarding
     */
    public function delete(int $domainId, int $forwardingId): bool;
}

