<?php

namespace App\Domain\Rdash\Forwarding\ValueObjects;

readonly class Forwarding
{
    public function __construct(
        public int $id,
        public int $domainId,
        public string $from, // @ or subdomain
        public string $to,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            domainId: $data['domain_id'] ?? $data['domainId'] ?? 0,
            from: $data['from'] ?? '',
            to: $data['to'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domainId,
            'from' => $this->from,
            'to' => $this->to,
            ...$this->metadata,
        ];
    }
}

