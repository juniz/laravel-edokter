<?php

namespace App\Domain\Rdash\Dns\ValueObjects;

readonly class DnsSec
{
    public function __construct(
        public int $id,
        public int $keytag, // 0-65535
        public int $algorithm, // 3, 5, 6, 7, 8, 10, 12, 13, 14
        public int $digesttype, // 1, 2
        public string $digest, // max 64 char
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            keytag: $data['keytag'] ?? 0,
            algorithm: $data['algorithm'] ?? 0,
            digesttype: $data['digesttype'] ?? 0,
            digest: $data['digest'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'keytag' => $this->keytag,
            'algorithm' => $this->algorithm,
            'digesttype' => $this->digesttype,
            'digest' => $this->digest,
            ...$this->metadata,
        ];
    }
}

