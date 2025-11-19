<?php

namespace App\Domain\Rdash\ObjectStorage\ValueObjects;

readonly class AccessKey
{
    public function __construct(
        public int $id,
        public string $accessKey,
        public string $secretKey,
        public ?string $label = null,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            accessKey: $data['access_key'] ?? $data['accessKey'] ?? '',
            secretKey: $data['secret_key'] ?? $data['secretKey'] ?? '',
            label: $data['label'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'access_key' => $this->accessKey,
            'secret_key' => $this->secretKey,
            'label' => $this->label,
            ...$this->metadata,
        ];
    }
}

