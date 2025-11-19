<?php

namespace App\Domain\Rdash\WhoisProtection\ValueObjects;

readonly class WhoisProtection
{
    public function __construct(
        public int $domainId,
        public bool $enabled,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            domainId: $data['domain_id'] ?? $data['domainId'] ?? 0,
            enabled: ($data['enabled'] ?? false) === true,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->domainId,
            'enabled' => $this->enabled,
            ...$this->metadata,
        ];
    }
}

