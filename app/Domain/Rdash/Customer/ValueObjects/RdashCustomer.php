<?php

namespace App\Domain\Rdash\Customer\ValueObjects;

readonly class RdashCustomer
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $organization,
        public Address $address,
        public ?string $phone = null,
        public ?string $fax = null,
        public ?string $regId = null,
        public bool $is2faEnabled = false,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            organization: $data['organization'] ?? '',
            address: Address::fromArray($data),
            phone: $data['voice'] ?? $data['phone'] ?? null,
            fax: $data['fax'] ?? null,
            regId: $data['reg_id'] ?? null,
            is2faEnabled: $data['is_2fa_enabled'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
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
            'street_1' => $this->address->street1,
            'street_2' => $this->address->street2,
            'city' => $this->address->city,
            'state' => $this->address->state,
            'country' => $this->address->country,
            'country_code' => $this->address->countryCode,
            'postal_code' => $this->address->postalCode,
            'voice' => $this->phone,
            'fax' => $this->fax,
            'reg_id' => $this->regId,
            'is_2fa_enabled' => $this->is2faEnabled,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            ...$this->metadata,
        ];
    }
}

