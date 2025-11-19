<?php

namespace App\Domain\Rdash\Customer\ValueObjects;

readonly class Address
{
    public function __construct(
        public string $street1,
        public ?string $street2 = null,
        public string $city,
        public ?string $state = null,
        public string $countryCode, // ISO 3166-1 alpha-2
        public string $postalCode,
        public ?string $country = null // Full country name
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            street1: $data['street_1'] ?? $data['street1'] ?? '',
            street2: $data['street_2'] ?? $data['street2'] ?? null,
            city: $data['city'] ?? '',
            state: $data['state'] ?? null,
            countryCode: $data['country_code'] ?? $data['countryCode'] ?? '',
            postalCode: $data['postal_code'] ?? $data['postalCode'] ?? '',
            country: $data['country'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'street_1' => $this->street1,
            'street_2' => $this->street2,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'country' => $this->country,
            'postal_code' => $this->postalCode,
        ];
    }
}

