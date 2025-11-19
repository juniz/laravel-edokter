<?php

namespace App\Application\Rdash\Domain;

use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;

class ListDomainsService
{
    public function __construct(
        private RdashDomainRepository $domainRepository
    ) {
    }

    /**
     * List all domains dengan filter
     *
     * @param array<string, mixed> $filters
     * @return array<int, RdashDomain>
     */
    public function execute(array $filters = []): array
    {
        return $this->domainRepository->getAll($filters);
    }
}

