<?php

namespace App\Domain\Rdash\Dns\ValueObjects;

readonly class DnsRecord
{
    public function __construct(
        public string $name,
        public string $type, // A, AAAA, MXE, MX, CNAME, SPF
        public string $content,
        public int $ttl = 3600,
        public ?int $priority = null, // For MX records
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            type: $data['type'] ?? '',
            content: $data['content'] ?? '',
            ttl: $data['ttl'] ?? 3600,
            priority: $data['priority'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'content' => $this->content,
            'ttl' => $this->ttl,
            'priority' => $this->priority,
            ...$this->metadata,
        ];
    }
}

