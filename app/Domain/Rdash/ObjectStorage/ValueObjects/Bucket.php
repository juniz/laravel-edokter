<?php

namespace App\Domain\Rdash\ObjectStorage\ValueObjects;

readonly class Bucket
{
    public function __construct(
        public string $name,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            ...$this->metadata,
        ];
    }
}

