<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Dns\Contracts\DnsRepository as DnsRepositoryContract;
use App\Domain\Rdash\Dns\ValueObjects\DnsRecord;
use App\Domain\Rdash\Dns\ValueObjects\DnsSec;
use App\Infrastructure\Rdash\HttpClient;

class DnsRepository implements DnsRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getRecords(int $domainId): array
    {
        $data = $this->client->get("/domains/{$domainId}/dns");
        $records = $data['records'] ?? [];

        return array_map(
            fn (array $record) => DnsRecord::fromArray($record),
            $records
        );
    }

    public function createRecords(int $domainId, array $records): bool
    {
        $data = [];
        foreach ($records as $index => $record) {
            $data["records[{$index}][name]"] = $record['name'];
            $data["records[{$index}][type]"] = $record['type'];
            $data["records[{$index}][content]"] = $record['content'];
            $data["records[{$index}][ttl]"] = $record['ttl'] ?? 3600;
        }

        $this->client->post("/domains/{$domainId}/dns", $data);

        return true;
    }

    public function updateRecord(int $domainId, array $record): bool
    {
        $this->client->put("/domains/{$domainId}/dns", $record);

        return true;
    }

    public function deleteRecord(int $domainId, array $record): bool
    {
        $this->client->delete("/domains/{$domainId}/dns/record", $record);

        return true;
    }

    public function deleteZone(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}/dns");

        return true;
    }

    public function enableDnssec(int $domainId): bool
    {
        $this->client->post("/domains/{$domainId}/dns/sec");

        return true;
    }

    public function disableDnssec(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}/dns/sec");

        return true;
    }

    public function getDnssec(int $domainId, array $filters = []): array
    {
        $data = $this->client->get("/domains/{$domainId}/dnssec", $filters);
        $dnssecs = $data['data'] ?? [];

        return array_map(
            fn (array $dnssec) => DnsSec::fromArray($dnssec),
            $dnssecs
        );
    }

    public function addDnssec(int $domainId, array $data): DnsSec
    {
        $response = $this->client->post("/domains/{$domainId}/dnssec", $data);

        return DnsSec::fromArray($response);
    }

    public function deleteDnssec(int $domainId, int $dnssecId): bool
    {
        $this->client->delete("/domains/{$domainId}/dnssec/{$dnssecId}");

        return true;
    }
}

