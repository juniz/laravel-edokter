<?php

namespace App\Application\Rdash\Domain;

use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\Domain\ValueObjects\DomainAvailability;

class CheckDomainAvailabilityService
{
    public function __construct(
        private RdashDomainRepository $domainRepository
    ) {
    }

    public function execute(string $domain, bool $includePremium = false): DomainAvailability
    {
        return $this->domainRepository->checkAvailability($domain, $includePremium);
    }
}

