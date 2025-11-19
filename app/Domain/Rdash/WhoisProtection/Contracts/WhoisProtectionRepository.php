<?php

namespace App\Domain\Rdash\WhoisProtection\Contracts;

use App\Domain\Rdash\WhoisProtection\ValueObjects\WhoisProtection;

interface WhoisProtectionRepository
{
    /**
     * Get whois protection by domain id
     */
    public function getByDomainId(int $domainId): ?WhoisProtection;

    /**
     * Buy whois protection
     */
    public function buy(int $domainId): WhoisProtection;

    /**
     * Enable whois protection
     */
    public function enable(int $domainId): bool;

    /**
     * Disable whois protection
     */
    public function disable(int $domainId): bool;
}

