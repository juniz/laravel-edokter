<?php

namespace App\Application\Rdash\Domain;

use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;

class GetDomainDetailsService
{
    public function __construct(
        private RdashDomainRepository $domainRepository
    ) {
    }

    /**
     * Get domain details by domain name
     */
    public function execute(string $domainName): ?RdashDomain
    {
        return $this->domainRepository->getByName($domainName);
    }

    /**
     * Get domain by ID
     */
    public function executeById(int $domainId): ?RdashDomain
    {
        return $this->domainRepository->getById($domainId);
    }
}

