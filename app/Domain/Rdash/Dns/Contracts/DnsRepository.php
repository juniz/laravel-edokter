<?php

namespace App\Domain\Rdash\Dns\Contracts;

use App\Domain\Rdash\Dns\ValueObjects\DnsRecord;
use App\Domain\Rdash\Dns\ValueObjects\DnsSec;

interface DnsRepository
{
    /**
     * Get all DNS records by domain id
     *
     * @return array<int, DnsRecord>
     */
    public function getRecords(int $domainId): array;

    /**
     * Create DNS records (replace all existing)
     *
     * @param array<int, array<string, mixed>> $records
     */
    public function createRecords(int $domainId, array $records): bool;

    /**
     * Update one DNS record
     *
     * @param array<string, mixed> $record
     */
    public function updateRecord(int $domainId, array $record): bool;

    /**
     * Delete DNS record
     *
     * @param array<string, mixed> $record
     */
    public function deleteRecord(int $domainId, array $record): bool;

    /**
     * Delete DNS zone
     */
    public function deleteZone(int $domainId): bool;

    /**
     * Enable DNSSEC
     */
    public function enableDnssec(int $domainId): bool;

    /**
     * Disable DNSSEC
     */
    public function disableDnssec(int $domainId): bool;

    /**
     * Get all DNSSEC records
     *
     * @param array<string, mixed> $filters
     * @return array<int, DnsSec>
     */
    public function getDnssec(int $domainId, array $filters = []): array;

    /**
     * Add DNSSEC record
     *
     * @param array<string, mixed> $data
     */
    public function addDnssec(int $domainId, array $data): DnsSec;

    /**
     * Delete DNSSEC record
     */
    public function deleteDnssec(int $domainId, int $dnssecId): bool;
}

