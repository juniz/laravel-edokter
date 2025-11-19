<?php

namespace App\Domain\Rdash\Domain\ValueObjects;

readonly class DomainWhois
{
    public function __construct(
        public string $domain,
        public array $data,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data['domain'] ?? '',
            data: $data['whois'] ?? $data['data'] ?? [],
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'whois' => $this->data,
            ...$this->metadata,
        ];
    }
}

