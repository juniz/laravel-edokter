<?php

namespace App\Domain\Rdash\Host\ValueObjects;

readonly class Host
{
    public function __construct(
        public int $id,
        public int $domainId,
        public string $hostname, // without domain name, ex: ns1
        public string $ipAddress, // IPv4 or IPv6
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            domainId: $data['domain_id'] ?? $data['domainId'] ?? 0,
            hostname: $data['hostname'] ?? '',
            ipAddress: $data['ip_address'] ?? $data['ipAddress'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domainId,
            'hostname' => $this->hostname,
            'ip_address' => $this->ipAddress,
            ...$this->metadata,
        ];
    }
}

