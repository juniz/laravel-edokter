<?php

namespace App\Domain\Rdash\Account\ValueObjects;

readonly class AccountProfile
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $organization = null,
        public ?string $phone = null,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            organization: $data['organization'] ?? null,
            phone: $data['phone'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'organization' => $this->organization,
            'phone' => $this->phone,
            ...$this->metadata,
        ];
    }
}

