<?php

namespace App\Domain\Rdash\Domain\Contracts;

use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;
use App\Domain\Rdash\Domain\ValueObjects\DomainAvailability;
use App\Domain\Rdash\Domain\ValueObjects\DomainWhois;

interface RdashDomainRepository
{
    /**
     * Get list all domains
     *
     * @param array<string, mixed> $filters
     * @return array<int, RdashDomain>
     */
    public function getAll(array $filters = []): array;

    /**
     * Get domain by id
     */
    public function getById(int $domainId): ?RdashDomain;

    /**
     * Get domain details by domain name
     */
    public function getByName(string $domainName): ?RdashDomain;

    /**
     * Check domain availability
     */
    public function checkAvailability(string $domain, bool $includePremium = false): DomainAvailability;

    /**
     * Get domain whois info
     */
    public function getWhois(string $domain): DomainWhois;

    /**
     * Register new domain
     *
     * @param array<string, mixed> $data
     */
    public function register(array $data): RdashDomain;

    /**
     * Transfer domain
     *
     * @param array<string, mixed> $data
     */
    public function transfer(array $data): RdashDomain;

    /**
     * Renew domain
     *
     * @param array<string, mixed> $data
     */
    public function renew(int $domainId, array $data): RdashDomain;

    /**
     * Update domain nameservers
     *
     * @param array<string> $nameservers
     */
    public function updateNameservers(int $domainId, array $nameservers, ?int $customerId = null): RdashDomain;

    /**
     * Update domain contacts
     *
     * @param array<string, mixed> $contacts
     */
    public function updateContacts(int $domainId, array $contacts): RdashDomain;

    /**
     * Get domain auth code
     */
    public function getAuthCode(int $domainId): string;

    /**
     * Reset domain auth code
     */
    public function resetAuthCode(int $domainId, string $authCode): bool;

    /**
     * Lock domain
     */
    public function lock(int $domainId, ?string $reason = null): bool;

    /**
     * Unlock domain
     */
    public function unlock(int $domainId): bool;

    /**
     * Registrar lock domain
     */
    public function registrarLock(int $domainId, ?string $reason = null): bool;

    /**
     * Registrar unlock domain
     */
    public function registrarUnlock(int $domainId): bool;

    /**
     * Suspend domain
     */
    public function suspend(int $domainId, int $type, string $reason): bool;

    /**
     * Unsuspend domain
     */
    public function unsuspend(int $domainId): bool;

    /**
     * Move domain to other customer
     */
    public function move(int $domainId, int $newCustomerId): bool;

    /**
     * Restore domain
     */
    public function restore(int $domainId): bool;

    /**
     * Cancel domain transfer
     *
     * @param array<string, mixed> $data
     */
    public function cancelTransfer(int $domainId, array $data): bool;

    /**
     * Resend domain verification email
     */
    public function resendVerification(int $domainId): bool;

    /**
     * Get document upload link
     */
    public function getDocumentUploadLink(int $domainId): string;

    /**
     * Delete domain
     */
    public function delete(int $domainId): bool;
}

